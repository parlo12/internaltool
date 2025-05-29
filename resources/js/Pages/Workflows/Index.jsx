import React, { useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import Sidebar from "@/Components/Sidebar";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faPen, faCopy, faFolderOpen } from "@fortawesome/free-solid-svg-icons";
import CopyWorkflowPopup from "@/Components/CopyWorkflowPopup";
import AssignFolderPopup from "@/Components/AssignFolderPopup";

export default function Index({ auth, contactGroups, workflows, folders, success, error, filters = {} }) {
    const [showFolderPopup, setShowFolderPopup] = useState(false);
    const [formData, setFormData] = useState({ folder_id: "", workflow_id: null });
    console.log("success", success);
    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleAssignFolderSubmit = async (e) => {
        e.preventDefault();

        try {
            await axios.post("/assign-folder", formData);
            setShowFolderPopup(false);
            // Optionally refresh data
        } catch (error) {
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            }
        }
    };



    const [showPopup, setShowPopup] = useState(false);
    const [copyData, setCopyData] = useState({
        workflow_name: "",
        contact_group: ""
    });
    const [selectedWorkflowId, setSelectedWorkflowId] = useState(null);
    const [errors, setErrors] = useState({});

    const [searchName, setSearchName] = useState(filters.search_name || "");

    const handleSearch = () => {
        router.get(
            route("workflows.index"),
            { search_name: searchName },
            { preserveState: true, preserveScroll: true }
        );
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Workflows" />
            <div className="flex min-h-screen bg-gray-50">
                <Sidebar />
                <div className="flex-1 container mx-auto px-4 py-8">
                    <div className="text-3xl font-bold text-center text-gray-800 mb-6">
                        Workflows
                    </div>
                    {/* Success and Error Messages */}
                    {success && (
                        <div className="bg-green-500 text-center text-white py-2 rounded shadow-md mb-4">
                            {success}
                        </div>
                    )}
                    {error && (
                        <div className="bg-red-500 text-center text-white py-2 rounded shadow-md mb-4">
                            {error}
                        </div>
                    )}
                    <div className="overflow-x-auto">
                        <table className="min-w-full bg-white shadow-md rounded-lg">
                            <thead>
                                <tr>
                                    <th className="px-6 py-3 bg-gray-100"></th>
                                    <th className="px-6 py-3 bg-gray-100">
                                        <input
                                            type="text"
                                            placeholder="Search Name"
                                            value={searchName}
                                            onChange={(e) => setSearchName(e.target.value)}
                                            onBlur={handleSearch}
                                            className="w-full p-1 border rounded text-sm"
                                        />
                                    </th>
                                    <th className="px-6 py-3 bg-gray-100"></th>
                                    <th className="px-6 py-3 bg-gray-100 text-sm font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {workflows.data.map((workflow) => (
                                    <tr key={workflow.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 text-sm text-gray-700">{workflow.id}</td>
                                        <td className="px-6 py-4 text-sm text-gray-700">{workflow.name}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">{workflow.contact_group}</td>
                                        <td className="py-4 text-right text-sm font-medium">
                                            <div className="flex flex-wrap justify-end space-x-2">
                                                <button
                                                    onClick={() => {
                                                        setCopyData({
                                                            workflow_name: workflow.name + "-copy",
                                                            contact_group: workflow.contact_group || ""
                                                        });
                                                        setSelectedWorkflowId(workflow.id);
                                                        setShowPopup(true);
                                                    }}
                                                    className="px-1 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                                >
                                                    <FontAwesomeIcon icon={faCopy} />
                                                </button>

                                                <Link
                                                    href="#"
                                                    onClick={(e) => {
                                                        e.preventDefault();
                                                        setFormData({ folder_id: "", workflow_id: workflow.id });
                                                        setShowFolderPopup(true);
                                                    }}
                                                >
                                                    <FontAwesomeIcon icon={faFolderOpen} />
                                                </Link>

                                                <Link
                                                    href={route("add_steps", workflow.id)}
                                                    className="px-1 py-1 bg-green-500 text-white rounded-md hover:bg-green-600"
                                                >
                                                    <FontAwesomeIcon icon={faPen} />
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        {/* Pagination Controls */}
                        <div className="flex justify-center items-center mt-4 space-x-2">
                            {workflows.links.map((link, index) => (
                                <button
                                    key={index}
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
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
            <CopyWorkflowPopup
                showPopup={showPopup}
                setShowPopup={setShowPopup}
                data={copyData}
                errors={errors}
                contactGroups={contactGroups}
                handleChange={({ target }) => {
                    setCopyData((prev) => ({ ...prev, [target.name]: target.value }));
                }}
                handleCopySubmit={(e) => {
                    e.preventDefault();
                    router.post(route("workflows.copy", selectedWorkflowId), copyData, {
                        onSuccess: () => {
                            setShowPopup(false);
                            setErrors({});
                        },
                        onError: (errorBag) => {
                            setErrors(errorBag);
                        },
                    });
                }}
            />
            <AssignFolderPopup
                showFolderPopup={showFolderPopup}
                setShowFolderPopup={setShowFolderPopup}
                data={formData}
                errors={errors}
                handleChange={handleChange}
                handleAssignFolderSubmit={handleAssignFolderSubmit}
                folders={folders}
            />

        </AuthenticatedLayout>

    );
}
