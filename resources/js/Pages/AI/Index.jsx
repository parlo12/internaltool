import { Link,Head } from "@inertiajs/react";
import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Index({ success, error, assistants,auth }) {
    const baseURL = import.meta.env.VITE_APP_URL;
    return ( // Add the return keyword
        <AuthenticatedLayout user={auth.user}>
        <Head title="Assistants" />
        <div className="container mx-auto px-4 py-8">
            {/* Success and Error Messages */}
            {success && (
                <div className="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded">
                    {success}
                </div>
            )}
            {error && (
                <div className="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded">
                    {error}
                </div>
            )}

            {/* Assistants Table */}
            <div className="overflow-x-auto mx-auto my-4 w-full max-w-8xl p-6 bg-white rounded-lg shadow-md">
                <Link
                    href="/assistants/create"
                    className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                >
                    Create New Assistant
                </Link>
                <table className="min-w-full border-collapse border border-gray-200">
                    <thead>
                        <tr className="bg-gray-100">
                            <th className="border border-gray-300 px-4 py-2 text-left">#</th>
                            <th className="border border-gray-300 px-4 py-2 text-left">Name</th>
                            <th className="border border-gray-300 px-4 py-2 text-left">Prompt</th>
                            <th className="border border-gray-300 px-4 py-2 text-left">File 1</th>
                            <th className="border border-gray-300 px-4 py-2 text-left">File 2</th>
                            <th className="border border-gray-300 px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {assistants.length > 0 ? (
                            assistants.map((assistant, index) => (
                                <tr
                                    key={assistant.id}
                                    className={index % 2 === 0 ? "bg-white" : "bg-gray-50"}
                                >
                                    <td className="border border-gray-300 px-4 py-2">{index + 1}</td>
                                    <td className="border border-gray-300 px-4 py-2">{assistant.name}</td>
                                    <td className="border border-gray-300 px-4 py-2">
                                        {assistant.prompt.split(" ").length > 50 ? (
                                            <>
                                                {assistant.prompt.split(" ").slice(0, 50).join(" ")}{" "}
                                                <span className="text-blue-600 font-semibold">  <a
                                            className="text-blue-500 hover:text-blue-700"
                                            href={route('assistants.view', assistant.id)}
                                        >
                                            <i className="fas fa-eye"></i> Read More
                                        </a></span>
                                            </>
                                        ) : (
                                            assistant.prompt
                                        )}
                                    </td>
                                    <td className="border border-gray-300 px-4 py-2">
                                        {assistant.file1 ? (
                                            
                                            <a href={`https://internaltools.godspeedoffers.com/uploads/${assistant.file1 }`} download>
                                            Download File 1
                                          </a>
                                        ) : (
                                            "N/A"
                                        )}
                                    </td>
                                    <td className="border border-gray-300 px-4 py-2">
                                        {assistant.file2 ? (
                                           
                                               <a href={`https://internaltools.godspeedoffers.com/uploads/${assistant.file2 }`} download>
                                               Download File 2
                                             </a>
                                        ) : (
                                            "N/A"
                                        )}
                                    </td>
                                    <td className="border border-gray-300 px-4 py-2 flex space-x-2">
                                        <a
                                            className="text-blue-500 hover:text-blue-700"
                                            href={route('assistants.view', assistant.id)}
                                        >
                                            <i className="fas fa-eye"></i> View
                                        </a>
                                        <a
                                            className="text-red-500 hover:text-red-700"
                                            href={ route('assistant.destroy',assistant.id)}
                                        >
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td
                                    colSpan="6"
                                    className="border border-gray-300 px-4 py-2 text-center"
                                >
                                    No assistants found.
                                </td>
                            </tr>
                        )}
                    </tbody>

                </table>
            </div>
        </div>
        </AuthenticatedLayout>
    );
}
