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
                        <div className="max-w-full ">
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className=" bg-white border-b border-gray-200 overflow-x-auto">
                                    <div className="max-h-96 overflow-y-auto">
                                        <table className="min-w-full divide-y divide-gray-200 text-sm sm:text-base">
                                            <thead>
                                                <tr>
                                                    <th className="px-4 sm:px-6 py-3 bg-gray-50 text-left font-medium text-gray-700 uppercase tracking-wider">
                                                        Name
                                                    </th>
                                                    <th className="px-4 sm:px-6 py-3 bg-gray-50 text-left font-medium text-gray-700 uppercase tracking-wider">
                                                        Contact Group
                                                    </th>
                                                    <th className="px-4 sm:px-6 py-3 bg-gray-50 text-left font-medium text-gray-700 uppercase tracking-wider">
                                                        Actions
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {workflowData.map((workflow) => (
                                                    <tr key={workflow.id}>
                                                        <td className="px-4 sm:px-6 py-4 whitespace-nowrap text-gray-900">
                                                            {workflow.name}
                                                        </td>
                                                        <td className="px-4 sm:px-6 py-4 whitespace-nowrap text-gray-500">
                                                            {workflow.contact_group}
                                                        </td>
                                                        <td className="px-4 sm:px-6 py-4 whitespace-nowrap text-right">
                                                            <button
                                                                onClick={() =>
                                                                    handleCopyClick(
                                                                        workflow
                                                                    )
                                                                }
                                                                className="inline-flex items-center px-3 sm:px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2"
                                                            >
                                                                <FontAwesomeIcon
                                                                    icon={faCopy}
                                                                    className="fa-xs"
                                                                />
                                                            </button>
                                                            <button
                                                                onClick={() =>
                                                                    handleAssignFolder(
                                                                        workflow
                                                                    )
                                                                }
                                                                className="inline-flex items-center px-3 sm:px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2"
                                                            >
                                                                <FontAwesomeIcon
                                                                    icon={
                                                                        faFolderOpen
                                                                    }
                                                                    className="fa-xs"
                                                                />
                                                            </button>
                                                            <Link
                                                                href={route(
                                                                    "add_steps",
                                                                    workflow.id
                                                                )}
                                                                className="inline-flex items-center px-3 sm:px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                            >
                                                                <FontAwesomeIcon
                                                                    icon={faPen}
                                                                    className="fa-xs"
                                                                />
                                                            </Link>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
