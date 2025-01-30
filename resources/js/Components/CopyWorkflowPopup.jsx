// CopyWorkflowPopup.js
import React from "react";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";

const CopyWorkflowPopup = ({
    showPopup,
    setShowPopup,
    data,
    errors,
    handleChange,
    handleCopySubmit,
    contactGroups,
}) => {
    if (!showPopup) return null;

    return (
        <div className="fixed inset-0 flex items-center justify-center z-50">
            <div className="bg-white p-4 rounded shadow-lg">
                <form onSubmit={handleCopySubmit}>
                    <div className="mb-4">
                        <InputLabel
                            htmlFor="workflow_name"
                            className="block text-sm font-medium"
                        >
                            Workflow Name
                        </InputLabel>
                        <TextInput
                            id="workflow_name"
                            required
                            name="workflow_name"
                            value={data.workflow_name}
                            onChange={(e) =>
                                handleChange({
                                    target: { name: "workflow_name", value: e.target.value },
                                })
                            }
                            className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                        />
                        <InputError
                            message={errors.workflow_name}
                            className="mt-2"
                        />
                    </div>
                    <div className="mb-4">
                        <InputLabel
                            htmlFor="contact_group"
                            className="block text-sm font-medium"
                        >
                            Contact Group
                        </InputLabel>
                        <select
                            id="contact_group"
                            required
                            name="contact_group"
                            value={data.contact_group}
                            onChange={handleChange}
                            className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                        >
                            <option value="">
                                Select Contact Group to send to
                            </option>
                            {contactGroups.map((group) => (
                                <option key={group.uid} value={group.uid}>
                                    {group.name}
                                </option>
                            ))}
                        </select>
                        <InputError
                            message={errors.contact_group}
                            className="mt-2"
                        />
                    </div>
                    <div className="mt-4 flex justify-end">
                        <PrimaryButton
                            type="button"
                            onClick={() => setShowPopup(false)}
                            className="mr-2"
                        >
                            Cancel
                        </PrimaryButton>
                        <PrimaryButton type="submit">
                            Copy Workflow
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CopyWorkflowPopup;
