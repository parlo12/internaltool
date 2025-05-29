import React, { useState } from "react";
import { Head, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import Sidebar from "@/Components/Sidebar";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faTrash, faEye } from "@fortawesome/free-solid-svg-icons";

export default function Index({ auth, folders, filters = {} }) {
    const [searchFolderName, setSearchFolderName] = useState(filters.search_folder || "");

    const handleSearch = () => {
        router.get(
            route("folders.index"),
            { search_folder: searchFolderName },
            { preserveState: true, preserveScroll: true }
        );
    };

    const deleteFolder = (deletedFolderId) => {
        if (!confirm("Are you sure you want to delete this folder?")) return;
        // You may want to use Inertia's delete here
        router.delete(`/delete-folder/${deletedFolderId}`);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Folders" />
            <div className="flex min-h-screen bg-gray-50">
                <Sidebar />
                <div className="flex-1 container mx-auto px-4 py-8">
                    <div className="text-3xl font-bold text-center text-gray-800 mb-6">
                        Folders
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full bg-white shadow-md rounded-lg">
                            <thead>
                                <tr>
                                    <th className="px-6 py-3 bg-gray-100 text-sm">ID</th>
                                    <th className="px-6 py-3 bg-gray-100 text-sm">
                                        <input
                                            type="text"
                                            placeholder="Search Name"
                                            value={searchFolderName}
                                            onChange={(e) => setSearchFolderName(e.target.value)}
                                            onBlur={handleSearch}
                                            className="w-full p-1 border rounded text-sm"
                                        />
                                    </th>
                                    <th className="px-6 py-3 bg-gray-100 text-sm">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {folders.data.map((folder) => (
                                    <tr key={folder.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-1 text-sm text-gray-700">{folder.id}</td>
                                        <td className="px-6 py-1 text-sm text-gray-700">{folder.name}</td>
                                        <td className="px-1 py-1 text-right text-sm font-medium">
                                            <div className="flex flex-wrap justify-end space-x-2">
                                                <button onClick={() => deleteFolder(folder.id)} className="px-1 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">
                                                    <FontAwesomeIcon icon={faTrash} />
                                                </button>
                                                <button className="px-1 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                                    <FontAwesomeIcon icon={faEye} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        {/* Pagination Controls */}
                        <div className="flex justify-center items-center mt-4 space-x-2">
                            {folders.links.map((link, index) => (
                                <button
                                    key={index}
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })}
                                    className={`px-3 py-1 rounded text-sm ${link.active
                                        ? "bg-blue-500 text-white"
                                        : "bg-gray-200 text-gray-700 hover:bg-gray-300"
                                        }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
