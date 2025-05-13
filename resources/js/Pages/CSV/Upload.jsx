import React, { useState } from "react";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import { Head, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Upload({ auth, success, zipfile, workflows }) {
    const { data, setData, post, errors, processing, reset } = useForm({
        csv_files: [],
        sms_workflow_id: "",
        calls_workflow_id: ""
    });

    const [fileNames, setFileNames] = useState([]);
    const [showWorkflowSelect, setShowWorkflowSelect] = useState(false);

    const handleFileChange = (e) => {
        const files = Array.from(e.target.files);
        setFileNames(files.map((file) => file.name));
        setData("csv_files", files);
    };

    const onSubmit = (e) => {
        e.preventDefault();
        const formData = new FormData();
        data.csv_files.forEach((file, index) => {
            formData.append(`csv_files[${index}]`, file);
        });
        formData.append("workflow_id", data.workflow_id);

        post(route("process.csv"), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                reset();
                setFileNames([]);
                setShowWorkflowSelect(false);
            },
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Upload CSV" />
            <div className="container mx-auto px-4 py-10 max-w-3xl">
                <div className="bg-white shadow-xl rounded-xl p-8 space-y-6">
                    {success && (
                        <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {success}
                        </div>
                    )}

                    <h2 className="text-3xl font-bold text-gray-800 text-center">
                        Upload CSV Files
                    </h2>

                    <form onSubmit={onSubmit} className="space-y-6">
                        <div>
                            <InputLabel htmlFor="csv-files">CSV Files</InputLabel>
                            <input
                                id="csv-files"
                                type="file"
                                accept=".csv"
                                multiple
                                required
                                onChange={handleFileChange}
                                className="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            {fileNames.length > 0 && (
                                <ul className="mt-2 text-sm text-gray-600 list-disc list-inside">
                                    {fileNames.map((name, idx) => (
                                        <li key={idx}>{name}</li>
                                    ))}
                                </ul>
                            )}
                            <InputError message={errors.csv_files} className="mt-2" />
                        </div>

                        <div className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="workflowToggle"
                                className="form-checkbox rounded text-indigo-600 focus:ring-indigo-500"
                                checked={showWorkflowSelect}
                                onChange={(e) => setShowWorkflowSelect(e.target.checked)}
                            />
                            <label htmlFor="workflowToggle" className="text-sm text-gray-700">
                                Create workflows too?
                            </label>
                        </div>

                        {showWorkflowSelect && (
                            <div className="space-y-4">
                                <div>
                                    <InputLabel htmlFor="sms_workflow_id">
                                        Copy Steps From (SMS Workflow)
                                    </InputLabel>
                                    <select
                                        id="sms_workflow_id"
                                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        value={data.sms_workflow_id}
                                        onChange={(e) => setData("sms_workflow_id", e.target.value)}
                                    >
                                        <option value="">-- Select Workflow --</option>
                                        {workflows.map((workflow) => (
                                            <option key={workflow.id} value={workflow.id}>
                                                {workflow.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.sms_workflow_id} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="calls_workflow_id">
                                        Copy Steps From (Calls Workflow)
                                    </InputLabel>
                                    <select
                                        id="calls_workflow_id"
                                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        value={data.calls_workflow_id}
                                        onChange={(e) => setData("calls_workflow_id", e.target.value)}
                                    >
                                        <option value="">-- Select Workflow --</option>
                                        {workflows.map((workflow) => (
                                            <option key={workflow.id} value={workflow.id}>
                                                {workflow.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.calls_workflow_id} className="mt-2" />
                                </div>
                            </div>
                        )}

                        <PrimaryButton
                            type="submit"
                            disabled={processing}
                            className="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-3 px-4 rounded-lg transition-colors duration-200"
                        >
                            {processing ? "Uploading..." : "Upload"}
                        </PrimaryButton>
                    </form>

                    {zipfile && (
                        <div className="text-center mt-6">
                            <p className="text-lg font-medium mb-2">Download your processed file:</p>
                            <a
                                className="text-indigo-600 hover:underline font-semibold text-base"
                                href={zipfile}
                                download
                            >
                                Download ZIP
                            </a>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
