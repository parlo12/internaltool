import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState, useCallback, useEffect } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import Papa from 'papaparse';
import ProgressBar from '@/Components/ProgressBar';

export default function ContactImport({ auth, workflows, fields, currentImportProgress, success, error }) {
    const [selectedFilename, setSelectedFilename] = useState("");
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);
    const [csvHeaders, setCsvHeaders] = useState([]);
    const [csvData, setCsvData] = useState([]);
    const [mappings, setMappings] = useState({});
    const [selectedWorkflowId, setSelectedWorkflowId] = useState("");

    const { post, processing } = useForm();
    const systemFields = [
        { name: 'phone', required: true },
        { name: 'contact_name', required: true },
        { name: 'address', required: false },
        { name: 'zipcode', required: false },
        { name: 'city', required: false },
        { name: 'state', required: false },
        { name: 'email', required: false },
        { name: 'age', required: false },
        { name: 'gender', required: false },
        { name: 'lead_score', required: false },
        { name: 'offer', required: false },
        { name: 'agent', required: false },
        { name: 'novation', required: false },
        { name: 'creative_price', required: false },
        { name: 'downpayment', required: false },
        { name: 'monthly', required: false },
        { name: 'generated_message', required: false },
        { name: 'earnest_money_deposit', required: false },
        { name: 'list_price', required: false },
    ];

    const allFields = [
        ...systemFields
        // ...fields.filter(f => !f.is_system).map(f => ({
        //     name: f.field_name,
        //     required: f.is_required
        // }))
    ];
    const groupProgressKey = `contactImportProgress.${auth?.user?.id}`;
    const [progress, setProgressState] = useState(() => {
        if (!auth?.user?.id) return null;
        const savedProgress = currentImportProgress;
        return savedProgress !== null ? JSON.parse(savedProgress) : null;
    });
    const setProgress = (value) => {
        setProgressState(value);
        if (value >= 100) {
            localStorage.removeItem(groupProgressKey);
        } else {
            localStorage.setItem(groupProgressKey, JSON.stringify(value));
        }
    };

    useEffect(() => {
        if (!auth?.user?.id) return;


        const channel = Echo.channel(`import.progress.${auth?.user?.id}`);

        channel.listen('.ContactImportProgress', (data) => {

            setProgress(data.progress);
        });

        return () => {
            Echo.leave(`import.progress.${auth?.user?.id}`);
            console.log(`Left channel import.progress.${auth?.user?.id}`);
        };
    }, [auth?.user?.id]);




    const handleFileUpload = useCallback((e) => {
        const file = e.target.files[0];
        if (!file) return;

        setSelectedFilename(file.name);

        let tempData = [];
        let headersSet = false;

        Papa.parse(file, {
            header: true,
            skipEmptyLines: true,
            chunk: (results) => {
                if (!headersSet && results.meta && results.meta.fields) {
                    setCsvHeaders(results.meta.fields);
                    headersSet = true;
                }
                tempData = [...tempData, ...results.data];
            },
            complete: () => {
                setCsvData(tempData);
            },
            error: (error) => {
                console.error('CSV parsing error:', error);
            }
        });

    }, []);

    const handleMappingChange = (fieldName, csvColumn) => {
        setMappings(prev => ({
            ...prev,
            [fieldName]: csvColumn
        }));
    };

    const validateMappings = () => {
        return allFields.filter(f =>
            f.required && !mappings[f.name]
        );
    };

    const transformData = () => {
        return csvData.map(row => {
            const mappedItem = { custom_attributes: {} };
            allFields.forEach(field => {
                const csvColumn = mappings[field.name];
                if (csvColumn && row[csvColumn]) {
                    if (systemFields.find(f => f.name === field.name)) {
                        mappedItem[field.name] = row[csvColumn];
                    } else {
                        mappedItem.custom_attributes[field.name] = row[csvColumn];
                    }
                }
            });
            return mappedItem;
        });
    };

    const handleSubmit = () => {
        if (!selectedWorkflowId) {
            alert('Please select a workflow to copy from before importing.');
            return;
        }
        const missingRequired = validateMappings();
        if (missingRequired.length > 0) {
            alert(`Missing required mappings: ${missingRequired.map(f => f.name).join(', ')}`);
            return;
        }
        const mappedData = transformData();
        console.log('Mapped Data:', mappedData);
        console.log('Mappings:', mappings);
        const mappingsArray = Object.entries(mappings).map(([field, column]) => ({
            field,
            column
        }));
        const formData = new FormData();
        formData.append('selected_workflow_id', selectedWorkflowId);
        formData.append('data', JSON.stringify(mappedData));
        formData.append('mappings', JSON.stringify(mappingsArray));
        formData.append('filename', selectedFilename);

        axios.post('/csv/import', {
            mappings: Object.entries(mappings).map(([field, column]) => ({ field, column })),
            data: transformData(),
            selected_workflow_id: selectedWorkflowId,
            filename: selectedFilename
        })
            .then(res => {
                setProgress(1);
                setCsvHeaders([]);
                setCsvData([]);
                setMappings({});
                // Optionally reset file input if needed
            })
            .catch(err => {
                console.error('Error:', err.response?.data);
            });

    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Import Contacts & Workflows" />

            <div className="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-gray-100 to-gray-300">
                <div className="w-full  py-16">
                    <div className="mx-auto px-8">
                        <div className="overflow-hidden bg-white shadow-2xl rounded-2xl border border-gray-300">
                            <div className="flex justify-end w-full max-w-7xl mx-auto mt-6">
                                <Link
                                    href={route('contact-import-failures.index')}
                                    className="inline-block px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded shadow transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-pink-400"
                                >
                                    View Import Failures
                                </Link>
                            </div>
                            <div className="p-8 text-black">
                                <h1 className="text-3xl font-extrabold mb-2 text-center text-gray-900 tracking-tight">Import Contacts & Create Workflows</h1>
                                <p className="text-md text-gray-700 mb-6 text-center">You can import contacts and create workflows at the same time from here.</p>
                                <div className="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
                                    <span className="font-semibold text-blue-700">Info:</span> As long as you use a valid US phone number, it will be automatically converted to the <span className="font-bold">E.164</span> format: <span className="font-mono">+123xxxxxxxxxx</span>.
                                </div>
                                {/* File Upload Section */}
                                <div className="mb-8 p-4 bg-onyx rounded-lg">
                                    {/* Select Workflow to Copy From */}

                                    <label className="block mb-4">
                                        <span className="text-sm font-medium mb-2 block">
                                            Upload CSV File
                                        </span>
                                        <input
                                            type="file"
                                            accept=".csv"
                                            onChange={handleFileUpload}
                                            className="block w-full text-sm text-snow
                                                file:mr-4 file:py-2 file:px-4
                                                file:rounded-full file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-outer-space file:text-snow
                                                hover:file:bg-dim-gray"
                                        />
                                    </label>
                                    <input type="hidden" name="filename" value={selectedFilename} />

                                    {/* Mapping Interface */}
                                    {csvHeaders.length > 0 && (
                                        <div className="mt-6">
                                            <h3 className="text-md text-black font-semibold mb-4">
                                                Map CSV Columns to Fields
                                            </h3>
                                            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                                                {allFields.map((field) => (
                                                    <div key={field.name} className="mb-4 flex items-center gap-4">
                                                        <div className="w-1/3">
                                                            <span className="font-medium text-black whitespace-normal break-words">
                                                                {field.name.split('_').map((part, idx, arr) => (
                                                                    <React.Fragment key={idx}>
                                                                        {part.charAt(0).toUpperCase() + part.slice(1)}
                                                                        {idx < arr.length - 1 ? <wbr /> : null}
                                                                    </React.Fragment>
                                                                ))}
                                                                {field.required && '*'}
                                                            </span>
                                                        </div>
                                                        <select
                                                            className="flex-1 bg-charcoal border border-dim-gray rounded px-3 py-2 text-black"
                                                            value={mappings[field.name] || ''}
                                                            onChange={(e) => handleMappingChange(field.name, e.target.value)}
                                                        >
                                                            <option value="">Select CSV Column</option>
                                                            {csvHeaders.map((header) => (
                                                                <option key={header} value={header}>
                                                                    {header}
                                                                </option>
                                                            ))}
                                                        </select>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                                <div className="mb-6">
                                    <label className="block mb-2 text-sm font-medium text-white">Select Workflow to Copy From</label>
                                    <select
                                        className="block w-full bg-charcoal border border-dim-gray rounded px-3 py-2 text-black"
                                        value={selectedWorkflowId || ''}
                                        required
                                        onChange={e => setSelectedWorkflowId(e.target.value)}
                                    >
                                        <option value="">-- Select Workflow --</option>
                                        {workflows && workflows.length > 0 && workflows.map(wf => (
                                            <option key={wf.id} value={wf.id}>{wf.name}</option>
                                        ))}
                                    </select>
                                </div>
                                {(progress !== null && progress < 100) && (
                                    <div className="mt-4 p-4 bg-onyx rounded-lg">
                                        <div className="mb-2 text-sm font-medium">
                                            Import Progress: {progress}%
                                        </div>
                                        <ProgressBar progress={progress} />
                                        <div className="mt-2 text-sm text-gray-400">
                                            Processed: {progress}% of contacts
                                        </div>
                                    </div>
                                )}
                                {/* Action Buttons */}
                                {csvHeaders.length > 0 && (
                                    <div className="mt-6 flex justify-end gap-4">
                                        <PrimaryButton
                                            onClick={handleSubmit}
                                            disabled={processing || (progress !== null && progress < 100)}
                                            className="bg-outer-space hover:bg-dim-gray"
                                        >
                                            {(progress !== null && progress < 100) ? 'Importing Please wait...' : 'Start Import'}
                                        </PrimaryButton>
                                    </div>
                                )}

                                {/* Status Messages */}
                                {success && (
                                    <div className="mt-4 p-4 rounded bg-green-500 text-white">
                                        {success}
                                    </div>
                                )}
                                {error && (
                                    <div className="mt-4 p-4 rounded bg-red-500 text-white">
                                        {error}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
