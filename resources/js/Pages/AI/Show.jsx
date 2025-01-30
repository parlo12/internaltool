import React, { useState } from "react";
import { useForm, Head } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import InputLabel from "@/Components/InputLabel";

export default function Show({ assistant, csrfToken, success, auth }) {
    // Initialize the form with the assistant data and CSRF token using Inertia's useForm
    const { data, setData, post, processing } = useForm({
        name: assistant.name || "",
        prompt: assistant.prompt || "",
        file1: null,
        file2: null,
        min_wait_time: assistant.min_wait_time || "",
        max_wait_time: assistant.max_wait_time || "",
        wait_time_units: assistant.wait_time_units || "",
        sleep_time: assistant.sleep_time || "",
        sleep_time_units: assistant.sleep_time_units || "",
        maximum_messages: assistant.maximum_messages || "",
        _token: csrfToken, // Add CSRF token to the form
    });

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setData({ ...data, [name]: value });
    };

    const handleFileChange = (e) => {
        const { name, files } = e.target;
        setData({ ...data, [name]: files[0] });
    };

    const handleSave = () => {
        // Use Inertia's post method to send the form data
        post(`/assistants/${assistant.id}/update`, data);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create-Assistant" />
            <div className="mx-auto my-4 w-full max-w-4xl p-6 bg-white rounded-lg shadow-md"> {/* Centered, shadow, and styling */}
                <div className="container mx-auto px-4 py-8">
                    {success && (
                        <div
                            className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert"
                        >
                            <span className="block sm:inline">{successMessage}</span>
                            <span className="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg
                                    className="fill-current h-6 w-6 text-green-500"
                                    role="button"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    onClick={(e) => e.currentTarget.parentElement.parentElement.remove()}
                                >
                                    <path d="M14.348 14.849a1 1 0 01-1.415 0L10 11.415l-2.933 2.934a1 1 0 01-1.415-1.415l2.933-2.934-2.933-2.934a1 1 0 011.415-1.415L10 8.585l2.933-2.934a1 1 0 111.415 1.415L11.415 10l2.933 2.934a1 1 0 010 1.415z" />
                                </svg>
                            </span>
                        </div>
                    )}
                    <h1 className="text-2xl font-bold mb-4">Edit Assistant</h1>
                    <form onSubmit={(e) => e.preventDefault()}>
                        {/* Name field */}
                        <div className="mb-4 mr-2 w-2/3">
                            <label className="block text-gray-700 font-semibold mb-2" htmlFor="name">
                                Name
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value={data.name}
                                onChange={handleInputChange}
                                className="w-full border border-gray-300 px-4 py-2 rounded"
                            />
                        </div>

                        {/* Prompt field */}
                        <div className="mb-4 mr-2 ">
                            <label className="block text-gray-700 font-semibold mb-2" htmlFor="prompt">
                                Prompt
                            </label>
                            <textarea
                                id="prompt"
                                name="prompt"
                                value={data.prompt}
                                onChange={handleInputChange}
                                className="w-full border border-gray-300 px-4 py-2 rounded"
                                rows="4"
                            />
                        </div>

                        {/* File 1 field */}
                        <div className="mb-4 mr-2 w-2/3">
                            <label className="block text-gray-700 font-semibold mb-2" htmlFor="file1">
                                File 1
                            </label>
                            <input
                                type="file"
                                id="file1"
                                name="file1"
                                onChange={handleFileChange}
                                className="w-full border border-gray-300 px-4 py-2 rounded"
                            />
                            {data.file1 && (
                                <a
                                    href={data.file1}
                                    className="text-blue-600 hover:underline mt-2 inline-block"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    View File 1
                                </a>
                            )}
                        </div>

                        {/* File 2 field */}
                        <div className="mb-4 mr-2 w-2/3">
                            <label className="block text-gray-700 font-semibold mb-2" htmlFor="file2">
                                File 2
                            </label>
                            <input
                                type="file"
                                id="file2"
                                name="file2"
                                onChange={handleFileChange}
                                className="w-full border border-gray-300 px-4 py-2 rounded"
                            />
                            {data.file2 && (
                                <a
                                    href={data.file2}
                                    className="text-blue-600 hover:underline mt-2 inline-block"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    View File 2
                                </a>
                            )}
                        </div>
                        <div className="flex  mt-4">
                            <div className="flex items-center mb-4">
                                <div className="mr-2 w-2/3">
                                    <InputLabel
                                        forInput="min_wait_time"
                                        value="Minimum Wait Time"
                                    />
                                    <input
                                        type="number"
                                        min="1" // Enforce minimum wait time limit
                                        name="min_wait_time"
                                        value={data.min_wait_time}
                                        required
                                        onChange={handleInputChange}
                                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                    />
                                </div>
                               
                            </div>
                            <div className="flex items-center">
                                <div className="mr-2 w-2/3">
                                    <InputLabel
                                        forInput="max_wait_time"
                                        value="Maximum Wait Time"
                                    />
                                    <input
                                        type="number"
                                        min={data.min_wait_time || 1} // Ensure max is greater than min
                                        name="max_wait_time"
                                        value={data.max_wait_time}
                                        required
                                        onChange={handleInputChange}
                                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                    />
                                </div>
                            </div>
                            <div>
                                    <InputLabel
                                        forInput="wait_time_units"
                                        value="Wait Time Units"
                                    />
                                    <select
                                        name="wait_time_units"
                                        onChange={handleInputChange}
                                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                        value={data.wait_time_units}
                                    >
                                        <option value="seconds">Seconds</option>
                                        <option value="minutes">Minutes</option>
                                        <option value="hours">Hours</option>
                                        <option value="days">Days</option>
                                    </select>
                                </div>
                        </div>

                        <div className="flex items-center mt-4">
                            <div className="mr-2 w-2/3">
                                <InputLabel
                                    forInput="sleep_time"
                                    value="Sleep Time After I Manually reply A Message"
                                />
                                <input
                                    type="number"
                                    min='1'
                                    name="sleep_time"
                                    value={data.sleep_time}
                                    required
                                    onChange={handleInputChange}
                                    className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    forInput="sleep_time_units"
                                    value="sleep_time_units"
                                />
                                <select
                                    name="sleep_time_units"
                                    onChange={handleInputChange}
                                    value={data.sleep_time_units}
                                    className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                >
                                    <option value="minutes">Minutes</option>
                                    <option value="hours">Hours</option>
                                    <option value="days">Days</option>
                                    <option value="seconds">Seconds</option>
                                </select>
                            </div>
                        </div>
                        <div className="flex items-center mt-4">
                            <div className="mr-2 w-2/3">
                                <InputLabel
                                    forInput="maximum_messages"
                                    value="Maximum Messages Assistant Can Reply To a Single Chat"
                                />
                                <input
                                    type="number"
                                    min='1'
                                    name="maximum_messages"
                                    value={data.maximum_messages}
                                    required
                                    onChange={handleInputChange}
                                    className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                />
                            </div>
                        </div>

                        {/* Save button */}
                        <div className="mt-6">
                            <button
                                type="button"
                                onClick={handleSave}
                                className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                disabled={processing} // Disable button while processing
                            >
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>

    );
}
