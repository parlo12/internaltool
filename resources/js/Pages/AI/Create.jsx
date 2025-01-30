import React from "react";
import { useForm, Head } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import InputLabel from "@/Components/InputLabel";

export default function Create({
    auth,
    success,
    error,


}) {
    const { data, setData, post, errors, progress } = useForm({
        name: "",
        prompt: "",
        file1: null,
        file2: null,
        min_wait_time:0,
        max_wait_time:0,
        wait_time_units:'minutes',
        sleep_time:0,
        sleep_time_units:"minutes",
        maximum_messages:0
    });

    const handleChange = (e) => {
        const { name, type, value, files } = e.target;
        setData(name, type === "file" ? files[0] : value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post("/assistants"); // Adjust the route if needed
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create-Assistant" />
            <div className="mx-auto my-4 w-full max-w-4xl p-6 bg-white rounded-lg shadow-md"> {/* Centered, shadow, and styling */}

            <div className="container mx-auto px-4 py-8">
                <h1 className="text-xl font-bold mb-4">Create a New Assistant</h1>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="mr-2 ">
                        <label className="block text-sm font-medium">Name</label>
                        <input
                            type="text"
                            name="name"
                            value={data.name}
                            onChange={handleChange}
                            className="w-full border border-gray-300 rounded px-3 py-2"
                            required
                        />
                        {errors.name && (
                            <span className="text-red-500 text-sm">{errors.name}</span>
                        )}
                    </div>
                    <div className="mr-2 ">
                        <label className="block text-sm font-medium">Prompt</label>
                        <textarea
                            name="prompt"
                            value={data.prompt}
                            onChange={handleChange}
                            className="w-full border border-gray-300 rounded px-3 py-2"
                            required
                        />
                        {errors.prompt && (
                            <span className="text-red-500 text-sm">{errors.prompt}</span>
                        )}
                    </div>
                    <div>
                        <label className="block text-sm font-medium">File 1</label>
                        <input
                            type="file"
                            name="file1"
                            onChange={handleChange}
                            className="w-full"
                        />
                        {errors.file1 && (
                            <span className="text-red-500 text-sm">{errors.file1}</span>
                        )}
                    </div>
                    <div className="mr-2 w-2/3">
                        <label className="block text-sm font-medium">File 2</label>
                        <input
                            type="file"
                            name="file2"
                            onChange={handleChange}
                            className="w-full"
                        />
                        {errors.file2 && (
                            <span className="text-red-500 text-sm">{errors.file2}</span>
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
                                        onChange={handleChange}
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
                                        onChange={handleChange}
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
                                        onChange={handleChange}
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
                                required
                                onChange={handleChange}
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
                                onChange={handleChange}
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
                                required
                                onChange={handleChange}
                                className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                            />
                        </div>
                    </div>
                    {progress && (
                        <div className="w-full bg-gray-200 rounded">
                            <div
                                className="bg-blue-500 text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded"
                                style={{ width: `${progress.percentage}%` }}
                            >
                                {progress.percentage}%
                            </div>
                        </div>
                    )}
                    <button
                        type="submit"
                        className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    >
                        Create Assistant
                    </button>
                </form>
            </div>
            </div>
        </AuthenticatedLayout>
    );
};

