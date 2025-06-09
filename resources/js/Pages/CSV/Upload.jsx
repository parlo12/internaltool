import React, { useState, useEffect } from "react";
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
    const [progressData, setProgressData] = useState({});
    const [activeJobs, setActiveJobs] = useState({});
    const [completedJobs, setCompletedJobs] = useState({});

    // Set up Echo listener when component mounts
    useEffect(() => {
        if (!auth.user || !window.Echo) return;

        const userId = auth.user.id;
        const channelName = `csv-progress.user.${userId}`;
        
        // Initialize Echo for private channel
        const channel = window.Echo.channel(channelName);
        
        // Listen for progress events
        channel.listen('.csv.progress', (event) => {
            console.log('Progress event received:', event);
            
            // Update progress data
            setProgressData(prev => ({
                ...prev,
                [event.jobId]: event
            }));

            // Track job status
            if (event.status === 'completed' || event.status === 'failed') {
                // Move to completed jobs
                setCompletedJobs(prev => ({
                    ...prev,
                    [event.jobId]: event
                }));
                
                // Remove from active jobs
                setActiveJobs(prev => {
                    const newJobs = {...prev};
                    delete newJobs[event.jobId];
                    return newJobs;
                });
            } else {
                // Keep in active jobs
                setActiveJobs(prev => ({
                    ...prev,
                    [event.jobId]: true
                }));
            }
        });

        // Log connection status
        window.Echo.connector.pusher.connection.bind('state_change', (states) => {
            console.log('Connection state changed:', states.current);
        });

        // Clean up listener when component unmounts
        return () => {
            if (channel) {
                channel.stopListening('.csv.progress');
                window.Echo.leaveChannel(channelName);
            }
        };
    }, [auth.user]);

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
        formData.append("sms_workflow_id", data.sms_workflow_id);
        formData.append("calls_workflow_id", data.calls_workflow_id);

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

    // Get status display properties
    const getStatusProperties = (status) => {
        switch (status) {
            case 'processing':
                return { color: 'bg-blue-600', text: 'Processing', textColor: 'text-blue-700' };
            case 'completed':
                return { color: 'bg-green-600', text: 'Completed', textColor: 'text-green-700' };
            case 'failed':
                return { color: 'bg-red-600', text: 'Failed', textColor: 'text-red-700' };
            default:
                return { color: 'bg-gray-600', text: 'Unknown', textColor: 'text-gray-700' };
        }
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

                    {/* Progress Display Section */}
                    <div className="space-y-4">
                        <h3 className="text-xl font-semibold text-gray-800">Processing Status</h3>
                        
                        {/* Active Jobs */}
                        {Object.keys(activeJobs).length > 0 && (
                            <div className="space-y-3">
                                {Object.keys(activeJobs).map(jobId => {
                                    const job = progressData[jobId] || {};
                                    const { color, text, textColor } = getStatusProperties(job.status);
                                    
                                    return (
                                        <div key={jobId} className="border border-gray-200 rounded-lg p-4">
                                            <div className="flex justify-between items-center mb-2">
                                                <span className="font-medium text-gray-700 truncate">
                                                    {job.fileName || 'Unknown file'}
                                                </span>
                                                <span className={`text-sm font-medium ${textColor}`}>
                                                    {text}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <div className="w-full bg-gray-200 rounded-full h-2.5 flex-1">
                                                    <div 
                                                        className={`h-2.5 rounded-full ${color}`} 
                                                        style={{ width: `${job.progress || 0}%` }}
                                                    ></div>
                                                </div>
                                                <span className="text-sm font-medium text-gray-600">
                                                    {job.progress || 0}%
                                                </span>
                                            </div>
                                            {job.message && (
                                                <p className="mt-2 text-sm text-gray-600">
                                                    {job.message}
                                                </p>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                        
                        {/* Completed Jobs */}
                        {Object.keys(completedJobs).length > 0 && (
                            <div className="space-y-3">
                                <h4 className="text-md font-medium text-gray-700">Recent Jobs</h4>
                                {Object.keys(completedJobs).map(jobId => {
                                    const job = completedJobs[jobId];
                                    const { textColor } = getStatusProperties(job.status);
                                    
                                    return (
                                        <div key={jobId} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <span className="truncate text-sm text-gray-600 flex-1 mr-2">
                                                {job.fileName || 'Unknown file'}
                                            </span>
                                            <span className={`text-sm font-medium ${textColor}`}>
                                                {job.status === 'completed' ? '✓ Completed' : '✗ Failed'}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                        
                        {Object.keys(activeJobs).length === 0 && Object.keys(completedJobs).length === 0 && (
                            <div className="text-center py-4 text-gray-500">
                                No active processing jobs
                            </div>
                        )}
                    </div>

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