import React, { useState } from "react";
import { Head, useForm } from "@inertiajs/react";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import Sidebar from "@/Components/Sidebar";

export default function Create({ auth, error }) {
    const { data, setData, post, errors, processing } = useForm({
        folder_name: "",
    });
    const [message, setMessage] = useState(null);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route("folder.store"), {
            onSuccess: () => setMessage("Folder created successfully!"),
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Folder" />
            <div className="flex min-h-screen bg-gray-50">
                <Sidebar />
                <div className="flex-1 container mx-auto px-4 py-8">
                    <div className="text-3xl font-bold text-center text-gray-800 mb-6">
                        Create a New Folder
                    </div>
                    {message && (
                        <div className="bg-green-500 text-center text-white py-2 rounded shadow-md mb-4">
                            {message}
                        </div>
                    )}
                    {error && (
                        <div className="bg-red-500 text-center text-white py-2 rounded shadow-md mb-4">
                            {error}
                        </div>
                    )}
                    <form onSubmit={handleSubmit} className="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
                        <div className="mb-4">
                            <InputLabel htmlFor="folder_name" className="block text-sm font-semibold text-gray-700">
                                Folder Name
                            </InputLabel>
                            <TextInput
                                id="folder_name"
                                required
                                name="folder_name"
                                value={data.folder_name}
                                onChange={e => setData("folder_name", e.target.value)}
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <InputError message={errors.folder_name} className="mt-2 text-red-500 text-sm" />
                        </div>
                        <div className="mt-4">
                            <PrimaryButton
                                type="submit"
                                disabled={processing}
                                className="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                {processing ? "Creating Folder..." : "Create"}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
