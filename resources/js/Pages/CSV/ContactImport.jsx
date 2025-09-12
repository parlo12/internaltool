import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState, useCallback, useEffect } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import Papa from 'papaparse';
import ProgressBar from '@/Components/ProgressBar';

export default function ContactImport({ auth, fields,currentImportProgress, success, error }) {
    console.log('auth:', auth);
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);
    const [csvHeaders, setCsvHeaders] = useState([]);
    const [csvData, setCsvData] = useState([]);
    const [mappings, setMappings] = useState({});
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

        console.log(`Listening to import.progress.${auth?.user?.id}`);

        const channel = Echo.channel(`import.progress.${auth?.user?.id}`);

        channel.listen('.ContactImportProgress', (data) => {
            console.log('Received ContactImportProgress event:data', data);
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
        formData.append('data', JSON.stringify(mappedData));
        formData.append('mappings', JSON.stringify(mappingsArray));

        axios.post('/csv/import', {
            mappings: Object.entries(mappings).map(([field, column]) => ({ field, column })),
            data: transformData()
        })
            .then(res => {
                setProgress(1);
                setCsvHeaders([]);
                setCsvData([]);
                setMappings({});
                // Optionally reset file input if needed
                console.log('Success:', res.data);
            })
            .catch(err => {
                console.error('Error:', err.response?.data);
            });

    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Import Contacts" />
            <div className="flex flex-col md:flex-row">
                <div className="w-full md:w-3/4 py-12">
                    <div className="mx-auto max-w-7xl sm:px-2 lg:px-4">
                        <div className="overflow-hidden bg-jet shadow-sm sm:rounded-lg border border-dark-gray">
                            <div className="text-white p-6">
                                {/* File Upload Section */}
                                <div className="mb-8 p-4 bg-onyx rounded-lg">
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

                                    {/* Mapping Interface */}
                                    {csvHeaders.length > 0 && (
                                        <div className="mt-6">
                                            <h3 className="text-md text-black font-semibold mb-4">
                                                Map CSV Columns to Fields
                                            </h3>
                                            {allFields.map((field) => (
                                                <div key={field.name} className="mb-4 flex items-center gap-4">
                                                    <div className="w-1/3">
                                                        <span className="font-medium text-black">
                                                            {field.name}{field.required && '*'}
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
                                    )}
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
