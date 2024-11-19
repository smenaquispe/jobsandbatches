import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import axios from 'axios';

export default function Dashboard() {
    const [orderCount, setOrderCount] = useState(1); // Número de trabajos
    const [batches, setBatches] = useState([]); // Para almacenar los batches
    const [currentBatchId, setCurrentBatchId] = useState(null); // Para almacenar el batch actual

    // Función para manejar el envío del formulario
    const startBatch = async () => {
        try {
            const response = await axios.post(route('batch.start'), 
                { order_count: orderCount }, 
                {
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    withCredentials: true, // Enviar cookies con la solicitud
                }
            );
    
            // Si la respuesta es correcta
            const data = response.data;
    
            // Suponiendo que la respuesta contiene un batchId
            const newBatch = { id: data.batchId, progress: 0 };
            setBatches([...batches, newBatch]);
            setCurrentBatchId(data.batchId);
            getBatchStatus(data.batchId); // Comenzamos a monitorear el progreso de este batch
        } catch (error) {
            console.error('Error al iniciar el batch', error);
        }
    };
    // Función para monitorear el progreso

    const getBatchStatus = (batchId) => {
        const intervalId = setInterval(async () => {
            try {
                const response = await axios.get(route('batch.status', { batchId }), {
                    withCredentials: true, // Enviar cookies con la solicitud
                });
    
                // Si la respuesta es correcta
                const data = response.data;
                console.log(data);
    
                // Actualizamos el progreso de todos los batches
                setBatches((prevBatches) =>
                    prevBatches.map((batch) =>
                        batch.id === batchId
                            ? { ...batch, progress: data.progress }
                            : batch
                    )
                );
    
                // Si el batch terminó, detenemos el intervalo
                if (data.isFinished) {
                    clearInterval(intervalId);
                }
            } catch (error) {
                console.error('Error al obtener el estado del batch', error);
            }
        }, 500); // Revisar cada 2 segundos
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h2 className="text-lg font-semibold mb-4">Batch Processing</h2>

                            {/* Formulario para iniciar el batch */}
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Number of Jobs</label>
                                <input
                                    type="number"
                                    value={orderCount}
                                    onChange={(e) => setOrderCount(Number(e.target.value))}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    min="1"
                                />
                            </div>

                            <button
                                onClick={startBatch}
                                className="px-4 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700"
                            >
                                Start Batch
                            </button>

                            {/* Tabla para mostrar el progreso de los batches */}
                            {batches.length > 0 && (
                                <div className="mt-6">
                                    <h3 className="font-semibold text-md">Batches Progress</h3>
                                    <table className="min-w-full table-auto mt-4 border-collapse border border-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="border px-4 py-2">Batch ID</th>
                                                <th className="border px-4 py-2">Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {batches.map((batch) => (
                                                <tr key={batch.id}>
                                                    <td className="border px-4 py-2">{batch.id}</td>
                                                    <td className="border px-4 py-2">{batch.progress}%</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

