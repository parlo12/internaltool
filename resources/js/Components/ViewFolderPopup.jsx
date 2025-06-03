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
    console.log(workflowData);
    useEffect(() => {
        // Define the URL of the route
        const url = `/folder-workflows/${data.folder_id}`;

        // Make the GET request
        axios
            .get(url)
            .then((response) => {
                console.log(response.data);
                // Update the state with the response data
                setWorkflowData(response.data.workflows);
                console.log(workflowData);
            })
            .catch((error) => {
                // Update the state with the error
                // setError("There was an error making the request!");
                console.error(error);
            });
    }, [showViewFolderPopup]);
    if (!showViewFolderPopup) return null;

    return (
        <div className="fixed inset-0 flex items-center justify-center z-10 bg-gray-800 bg-opacity-50">
            <div className="bg-white p-6 rounded-lg shadow-lg w-full max-w-4xl mx-4 sm:mx-auto">
                <div>
                    {workflowData && (
                        <div className="overflow-x-auto max-w-full">
                            <table className="min-w-full table-auto bg-white shadow-md rounded-lg text-sm">
                                <thead>
                                    <tr>
                                        <th className="px-2 py-1 bg-gray-100 text-left w-12 max-w-[60px]">ID</th>
                                        {/* <th className="px-2 py-1 bg-gray-100 text-left max-w-[120px]">
                                            <input
                                                type="text"
                                                placeholder="Search Name"
                                                value={searchName}
                                                onChange={(e) => setSearchName(e.target.value)}
                                                className="w-full p-1 border rounded text-xs"
                                            />
                                        </th> */}
                                        <th className="px-2 py-1 bg-gray-100 text-left hidden md:table-cell max-w-[120px]">Contact Group</th>
                                        <th className="px-2 py-1 bg-gray-100 max-w-[60px]">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {workflowData.map((workflow) => (
                                        <tr key={workflow.id} className="hover:bg-gray-50">
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
