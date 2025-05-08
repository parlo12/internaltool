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
            <div className="container mt-5 mx-auto min-h-screen">
                <div className="flex flex-col items-center justify-center min-h-screen">
                    <div className="w-full max-w-md p-4 bg-white shadow-md rounded-md mb-6">
                        {success && (
                            <div className="bg-green-500 text-center text-white p-2 rounded mb-4">
                                {success}
                            </div>
                        )}
                        <h2 className="text-2xl font-semibold text-center mb-6">Upload CSV Files</h2>
                        <form onSubmit={onSubmit}>
                            <div className="mb-4">
                                <InputLabel htmlFor="csv-files" className="block text-sm font-medium">
                                    CSV Files
                                </InputLabel>
                                <input
                                    id="csv-files"
                                    type="file"
                                    accept=".csv"
                                    multiple
                                    required
                                    onChange={handleFileChange}
                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                />
                                {fileNames.length > 0 && (
                                    <ul className="text-sm text-gray-600 mt-2">
                                        {fileNames.map((name, idx) => (
                                            <li key={idx}>• {name}</li>
                                        ))}
                                    </ul>
                                )}
                                <InputError message={errors.csv_files} className="mt-2" />
                            </div>

                            <div className="mb-4 mt-4">
                                <label className="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        className="form-checkbox"
                                        checked={showWorkflowSelect}
                                        onChange={(e) => setShowWorkflowSelect(e.target.checked)}
                                    />
                                    <span className="text-sm">Would you like to create workflows too?</span>
                                </label>
                            </div>

                            {showWorkflowSelect && (
                                <>
                                    <div className="mb-4">
                                        <InputLabel htmlFor="workflow_id" className="block text-sm font-medium">
                                            Choose Workflow To Copy Steps From for SMS
                                        </InputLabel>
                                        <select
                                            id="sms_workflow_id"
                                            name="sms_workflow_id"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
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
                                    <div className="mb-4">
                                        <InputLabel htmlFor="calls_workflow_id" className="block text-sm font-medium">
                                            Choose Workflow To Copy Steps From for Calls
                                        </InputLabel>
                                        <select
                                            id="calls_workflow_id"
                                            name="calls_workflow_id"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
                                            value={data.workflow_id}
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

                                </>

                            )}

                            <div className="flex justify-end">
                                <PrimaryButton type="submit" disabled={processing} className="w-full">
                                    {processing ? "Uploading..." : "Upload"}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>

                    {zipfile && (
                        <div>
                            <p className="text-lg font-semibold text-center mb-2">
                                Download your processed file:
                            </p>
                            <a className="text-2xl text-blue-700" href={zipfile} download>
                                Download {zipfile}
                            </a>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
