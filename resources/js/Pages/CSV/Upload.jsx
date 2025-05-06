import React, { useState } from "react";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import { Head, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Upload({ auth, success, csvFiles, zipfile }) {
    const { data, setData, post, errors, processing, reset } = useForm({
        csv_files: [], // plural, for multiple files
    });

    const [fileNames, setFileNames] = useState([]);

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

        post(route("process.csv"), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                reset();
                setFileNames([]);
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
