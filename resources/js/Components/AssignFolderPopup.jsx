// CopyWorkflowPopup.js
import React from "react";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";

const AsignFolderPopup = ({
    showFolderPopup,
    setShowFolderPopup,
    data,
    errors,
    handleChange,
    handleAssignFolderSubmit,
    folders,
}) => {
    if (!showFolderPopup) return null;

    return (
        <div className="fixed inset-0 flex items-center justify-center z-50">
            <div className="bg-white p-4 rounded shadow-lg">
                <form onSubmit={handleAssignFolderSubmit}>
                    <div className="mb-4">
                        <InputLabel
                            htmlFor="folder"
                            className="block text-sm font-medium"
                        >
                            Folder
                        </InputLabel>
                        <select
                            id="folder_id"
                            required
                            name="folder_id"
                            value={data.folder_id}
                            onChange={handleChange}
                            className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                        >
                            <option value="">
                                Select Folder
                            </option>
                            {folders.map((folder) => (
                                <option key={folder.id} value={folder.id}>
                                    {folder.name}
                                </option>
                            ))}
                        </select>
                        <InputError
                            message={errors.folder}
                            className="mt-2"
                        />
                    </div>
                    <div className="mt-4 flex justify-end">
                        <PrimaryButton
                            type="button"
                            onClick={() => setShowFolderPopup(false)}
                            className="mr-2"
                        >
                            Cancel
                        </PrimaryButton>
                        <PrimaryButton type="submit">
                            Assign Folder
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default AsignFolderPopup;
