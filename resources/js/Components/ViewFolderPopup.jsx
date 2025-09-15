// CopyWorkflowPopup.js
import React, { useState, useEffect } from "react";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
    faPen,
    faCopy,
    faFolderOpen,
    faEye,
    faTrash,
} from "@fortawesome/free-solid-svg-icons";
import { Link } from "@inertiajs/react";
const CopyWorkflowPopup = ({
    showViewFolderPopup,
    setShowViewFolderPopup,
    data,
    handleCopyClick,
    handleAssignFolder
}) => {
    const [workflowData, setWorkflowData] = useState(null);
    const [selectedWorkflows, setSelectedWorkflows] = useState([]);
    const [selectAll, setSelectAll] = useState(false);
    const [searchName, setSearchName] = useState("");

    useEffect(() => {
        // Define the URL of the route
        const url = `/folder-workflows/${data.folder_id}`;

        // Make the GET request
        axios
            .get(url)
            .then((response) => {
                // Update the state with the response data
                setWorkflowData(response.data.workflows);
            })
            .catch((error) => {
                // Update the state with the error
                // setError("There was an error making the request!");
                console.error(error);
            });
    }, [showViewFolderPopup]);

    const handleWorkflowSelect = (id) => {
        setSelectedWorkflows((prev) =>
            prev.includes(id) ? prev.filter((wid) => wid !== id) : [...prev, id]
        );
    };

    const handleSelectAll = () => {
        if (selectAll) {
            setSelectedWorkflows([]);
        } else if (workflowData) {
            setSelectedWorkflows(workflowData.map((w) => w.id));
        }
        setSelectAll(!selectAll);
    };

    const handleMassDelete = () => {
        if (selectedWorkflows.length === 0) return;
        if (window.confirm(`Are you sure you want to delete ${selectedWorkflows.length} workflow(s)?`)) {
            axios.post('/delete-multiple-workflows', { ids: selectedWorkflows })
                .then(() => {
                    setSelectedWorkflows([]);
                    setWorkflowData((prev) => prev.filter(w => !selectedWorkflows.includes(w.id)));
                });
        }
    };

    const handleRemoveFromFolder = (workflowId) => {
        if (!window.confirm('Remove this workflow from the folder?')) return;
        axios.post('/remove-workflow-from-folder', { workflow_id: workflowId, folder_id: data.folder_id })
            .then(() => {
                setWorkflowData((prev) => prev.filter(w => w.id !== workflowId));
                setSelectedWorkflows((prev) => prev.filter(id => id !== workflowId));
            });
    };

    if (!showViewFolderPopup) return null;

    return (
        <div className="fixed inset-0 flex items-center justify-center z-10 bg-gray-800 bg-opacity-50">
            <div className="bg-white p-6 rounded-lg shadow-lg w-full max-w-4xl mx-4 sm:mx-auto">
                <div>
                    {workflowData && (
                        <div className="overflow-x-auto max-w-full" style={{ height: '75vh', overflowY: 'auto' }}>
                            <div className="flex items-center justify-between mb-2">
                                <span className="font-semibold text-gray-700">Workflows in Folder</span>
                                <button
                                    onClick={handleMassDelete}
                                    disabled={selectedWorkflows.length === 0}
                                    className={`ml-2 px-3 py-1 rounded text-xs font-semibold ${selectedWorkflows.length === 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-red-500 text-white hover:bg-red-600'}`}
                                >
                                    Delete Selected
                                </button>
                            </div>
                            <div className="mb-2 flex items-center gap-2">
                                <input
                                    type="text"
                                    placeholder="Search workflow name..."
                                    value={searchName}
                                    onChange={e => setSearchName(e.target.value)}
                                    className="w-64 p-1 border rounded text-xs"
                                />
                            </div>
                            <table className="min-w-full table-auto bg-white shadow-md rounded-lg text-sm">
                                <thead>
                                    <tr>
                                        <th className="px-2 py-1 bg-gray-100 text-center w-8 max-w-[32px]">
                                            <input
                                                type="checkbox"
                                                checked={selectAll}
                                                onChange={handleSelectAll}
                                            />
                                        </th>
                                        <th className="px-2 py-1 bg-gray-100 text-left w-12 max-w-[60px]">ID</th>
                                        <th className="px-2 py-1 bg-gray-100 text-left max-w-[120px]">Name</th>
                                        <th className="px-2 py-1 bg-gray-100 text-left hidden md:table-cell max-w-[120px]">Contact Group</th>
                                        <th className="px-2 py-1 bg-gray-100 max-w-[60px]">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {(workflowData.filter(w => w.name.toLowerCase().includes(searchName.toLowerCase()))).map((workflow) => (
                                        <tr key={workflow.id} className="hover:bg-gray-50">
                                            <td className="px-2 py-1 text-center max-w-[32px]">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedWorkflows.includes(workflow.id)}
                                                    onChange={() => handleWorkflowSelect(workflow.id)}
                                                />
                                            </td>
                                            <td className="px-2 py-1 text-gray-700 max-w-[60px] break-words whitespace-pre-wrap">{workflow.id}</td>
                                            <td className="px-2 py-1 text-gray-700 max-w-[120px] break-words whitespace-pre-wrap">{workflow.name}</td>
                                            <td className="px-2 py-1 text-gray-500 hidden md:table-cell max-w-[120px] break-words whitespace-pre-wrap">{workflow.contact_group}</td>
                                            <td className="px-1 py-1 max-w-[80px]">
                                                <div className="flex justify-center gap-1">
                                                    <button
                                                        onClick={() => handleCopyClick(workflow)}
                                                        className="p-1 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                                    >
                                                        <FontAwesomeIcon icon={faCopy} />
                                                    </button>
                                                    <button
                                                        onClick={() => handleAssignFolder(workflow)}
                                                        className="p-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                                    >
                                                        <FontAwesomeIcon icon={faFolderOpen} />
                                                    </button>
                                                    <Link
                                                        href={route("add_steps", workflow.id)}
                                                        className="p-1 bg-green-500 text-white rounded-md hover:bg-green-600"
                                                    >
                                                        <FontAwesomeIcon icon={faPen} />
                                                    </Link>
                                                    <button
                                                        onClick={() => handleRemoveFromFolder(workflow.id)}
                                                        className="p-1 bg-gray-500 text-white rounded-md hover:bg-gray-700"
                                                        title="Remove from folder"
                                                    >
                                                        Remove
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
                <div className="mt-4 flex justify-end">
                    <PrimaryButton
                        type="button"
                        onClick={() => setShowViewFolderPopup(false)}
                        className="mr-2"
                    >
                        Cancel
                    </PrimaryButton>
                </div>
            </div>
        </div>
    );
};

export default CopyWorkflowPopup;
