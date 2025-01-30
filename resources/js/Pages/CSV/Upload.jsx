import React, { useState, useEffect } from "react";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import { Head, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Upload({ auth, success, csvFiles, zipfile }) {
    const { data, setData, post, errors, processing, reset } = useForm({
        csv_file: null, // Default state for the file input
    });
console.log(zipfile);
    const [fileName, setFileName] = useState("");

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        setFileName(file ? file.name : "");
        setData("csv_file", file);
    };

    const onSubmit = (e) => {
        e.preventDefault();

        const formData = new FormData();
        formData.append("csv_file", data.csv_file);

        post(route("process.csv"), formData, {
            onSuccess: () => {
                reset();
                setFileName(""); // Reset file name after upload
                // Optionally, refetch CSV files
                fetch(route("csv-files.index"))
                    .then((response) => response.json())
                    .then((data) => setCsvFiles(data));
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
                        <h2 className="text-2xl font-semibold text-center mb-6">
                            Upload CSV File
                        </h2>
                        <form onSubmit={onSubmit}>
                            <div className="mb-4">
                                <InputLabel
                                    htmlFor="csv-file"
                                    className="block text-sm font-medium"
                                >
                                    CSV File
                                </InputLabel>
                                <input
                                    id="csv-file"
                                    type="file"
                                    accept=".csv"
                                    required
                                    onChange={handleFileChange}
                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                />
                                {fileName && (
                                    <p className="text-sm text-gray-600 mt-2">
                                        Selected file: {fileName}
                                    </p>
                                )}
                                <InputError
                                    message={errors.csv_file}
                                    className="mt-2"
                                />
                            </div>
                            <div className="flex justify-end">
                                <PrimaryButton
                                    type="submit"
                                    disabled={processing}
                                    className="w-full"
                                >
                                    {processing ? "Uploading..." : "Upload"}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                    {zipfile && (
                <div>
                    <p classname="h1 text-center">Download your processed file:</p>
                    <a className="text-2xl text-blue-700 " href={zipfile} download>
                        Download  {zipfile}
                    </a>
                </div>
            )}
                    {/* Table Section */}
                    {/* <div className="w-full max-w-4xl bg-white shadow-md rounded-md p-4">
                        <h3 className="text-xl font-semibold mb-4">My CSV Files</h3>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Original Filename
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Wireless csv
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Landline only csv
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Processed landline csv
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            No Usage csv
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {csvFiles.length > 0 ? (
                                        csvFiles.map((file) => (
                                            <tr key={file.id}>
                                                <td className="px-6 py-4 text-sm text-gray-900">
                                                    {file.original_filename}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-gray-500">
                                                        <a
                                                        href={`https://internaltools.godspeedoffers.com/uploads/${file.original_filename}_wireless_numbers.csv`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            download
                                                            className="text-indigo-600 hover:underline"
                                                        >
                                                            Download {file.original_filename} Wireless CSV
                                                        </a>
                                                </td>
                                                <td className="px-6 py-4 text-sm text-gray-500">
                                                        <a
                                                        href={`https://internaltools.godspeedoffers.com/uploads/${file.original_filename}_landline_only_numbers.csv`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            download
                                                            className="text-indigo-600 hover:underline"
                                                        >
                                                            Download {file.original_filename} Landline Only CSV
                                                        </a>
                                                </td>
                                                <td className="px-6 py-4 text-sm text-gray-500">
                                                        <a
                                                        href={`https://internaltools.godspeedoffers.com/uploads/${file.original_filename}_processed_numbers.csv`}
                                                        target="_blank"
                                                            rel="noopener noreferrer"
                                                            download
                                                            className="text-indigo-600 hover:underline"
                                                        >
                                                            Download {file.original_filename} Processed Landline CSV
                                                        </a>
                                                </td>
                                                <td className="px-6 py-4 text-sm text-gray-500">
                                                        <a
                                                        href={`https://internaltools.godspeedoffers.com/uploads/${file.original_filename}_no_usage_numbers.csv`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            download
                                                            className="text-indigo-600 hover:underline"
                                                        >
                                                            Download {file.original_filename} No usage CSV
                                                        </a>
                                                
                                                </td>

                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan="5"
                                                className="px-6 py-4 text-center text-sm text-gray-500"
                                            >
                                                No files uploaded yet.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div> */}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
