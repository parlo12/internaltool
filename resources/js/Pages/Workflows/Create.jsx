import React, { useState, useEffect } from "react";
import { Head, Link, useForm, router } from "@inertiajs/react";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import Tooltip from "@/Components/Tooltip";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
    faPen,
    faCopy,
    faFolderOpen,
    faEye,
    faTrash,
} from "@fortawesome/free-solid-svg-icons";
import CopyWorkflowPopup from "@/Components/CopyWorkflowPopup";
import AssignFolderPopup from "@/Components/AssignFolderPopup";
import ViewFolderPopup from "@/Components/ViewFolderPopup";

export default function Create({
    success,
    auth,
    contactGroups,
    workflows,
    voices,
    calling_numbers,
    texting_numbers,
    numberPools,
    folders,
    error,
    organisation,
    filters = {}
}) {
    const [searchName, setSearchName] = useState(filters.search_name || "");
    const [debounceTimer, setDebounceTimer] = useState(null);
    const [searchFolderName, setSearchFolderName] = useState("");
    const [loading, setLoading] = useState(false);

    const handleFolderSearch = () => {
        setLoading(true);

        router.get(route("create-workflow"), { search_folder: searchFolderName }, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => setLoading(false),

        });
    };

    useEffect(() => {
        if (debounceTimer) clearTimeout(debounceTimer);
        const timer = setTimeout(() => {
            handleFolderSearch();
        }, 500);
        setDebounceTimer(timer);
        return () => clearTimeout(timer);
    }, [searchFolderName]);

    const handleSearch = () => {
        console.log("Searching for:", searchName);
        router.get(
            route("create-workflow"),
            { search_name: searchName },
            {
                preserveState: true,
                preserveScroll: true, // 👈 preserve scroll position
            }
        );
    };

    // Debounce search input
    useEffect(() => {
        if (debounceTimer) clearTimeout(debounceTimer);
        const timer = setTimeout(() => {
            handleSearch();
        }, 1000); // 500ms debounce

        setDebounceTimer(timer);
        return () => clearTimeout(timer);
    }, [searchName]);
    const { data, setData, post, errors, processing } = useForm({
        name: "",
        contact_group: "",
        voice: "",
        agent_phone_number: "",
        country_code: "+1",
        calling_number: "",
        texting_number: "",
        id: "",
        workflow_name: "",
        folder_name: "",
        folder_id: "",
        number_pool_id: "",
        generated_message: '0'
    });

    const [showPopup, setShowPopup] = useState(false);
    const [showFolderPopup, setShowFolderPopup] = useState(false);
    const [showViewFolderPopup, setShowViewFolderPopup] = useState(false);
    const [message, setMessage] = useState(null);
    const [errorMessage, setErrorMessage] = useState(null);
    const [copyData, setCopyData] = useState({
        id: null,
        workflow_name: "",
    });
    const [selectedWorkflows, setSelectedWorkflows] = useState([]);
    const [selectAll, setSelectAll] = useState(false);

    const handleWorkflowSelect = (id) => {
        setSelectedWorkflows((prev) =>
            prev.includes(id) ? prev.filter((wid) => wid !== id) : [...prev, id]
        );
    };

    const handleSelectAll = () => {
        if (selectAll) {
            setSelectedWorkflows([]);
        } else {
            setSelectedWorkflows(workflows.data.map((w) => w.id));
        }
        setSelectAll(!selectAll);
    };

    const handleMassDelete = () => {
        if (selectedWorkflows.length === 0) return;
        if (window.confirm(`Are you sure you want to delete ${selectedWorkflows.length} workflow(s)?`)) {
            router.post('/delete-multiple-workflows', { ids: selectedWorkflows }, {
                onSuccess: () => setSelectedWorkflows([])
            });
        }
    };

    const validatePhoneNumber = (phoneNumber) => {
        return true;
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (!validatePhoneNumber(data.agent_phone_number)) {
            alert("Please enter a valid phone number.");
            return;
        }
        post("/store-workflow");
    };

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    const handleCopyClick = (workflow) => {
        setData({
            id: workflow.id,
            workflow_name: `${workflow.name}-copy`,
            contact_group: data.contact_group,
        });
        setShowPopup(true);
    };
    const handleViewFolder = (folder) => {
        setData({
            folder_id: folder.id,
        });
        setShowViewFolderPopup(true);
    };
    const handleAssignFolder = (workflow) => {
        console.log("Working with".workflow);
        setData({
            id: workflow.id,
        });
        setShowFolderPopup(true);
    };
    const handleAssignFolderSubmit = async (e) => {
        e.preventDefault();
        try {
            await post("/assign-folder");
            setShowFolderPopup(false);
        } catch (error) {
            setErrorMessage("Error assigning folder. Please try again.");
            console.error("Error assigning folder:", error.response?.data || error.message);
        }
    };

    const handleCopySubmit = (e) => {
        e.preventDefault();
        post("/copy-workflow");
        setShowPopup(false);
    };
    const createFolder = (e) => {
        e.preventDefault();
        post("/create-folder");
        setShowPopup(false);
    };
    const deleteFolder = (deletedFolderId) => {
        if (!confirm("Are you sure you want to delete this folder?")) {
            return;
        }
        axios
            .delete(`/delete-folder/${deletedFolderId}`, {})
            .then((response) => {
                console.log(`Folder deleted successfully`);
                setMessage(`Folder deleted successfully`);
                location.reload();
            })
            .catch((error) => {
                setErrorMessage("Error Deleting Folder");
                console.error(
                    "Error Deleting Folder",
                    error.response?.data || error.message
                );
            });
    };

    useEffect(() => {
        if (message) {
            const timer = setTimeout(() => {
                setMessage(null);
            }, 3000);
            return () => clearTimeout(timer);
        }
    }, [message]);

    useEffect(() => {
        if (errorMessage) {
            const timer = setTimeout(() => {
                setErrorMessage(null);
            }, 3000);
            return () => clearTimeout(timer);
        }
    }, [errorMessage]);
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create workflow" />
            <div className="mx-auto max-w-full py-8 min-h-screen">
                <div className="w-full p-4">
                    {success && (
                        <div className="bg-green-500 text-center text-white py-2 rounded shadow-md">
                            {success}
                        </div>
                    )}
                    {message && (
                        <div className="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-green-500 text-white p-4 rounded shadow-lg">
                            {message}
                        </div>
                    )}
                    {errorMessage && (
                        <div className="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-500 text-white p-4 rounded shadow-lg">
                            {errorMessage}
                        </div>
                    )}
                    {error && (
                        <div className="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-500 text-white p-4 rounded shadow-lg">
                            {error}
                        </div>
                    )}
                    <div className="text-3xl font-bold text-center text-gray-800 mb-6">
                        Create a New Workflow for {organisation?.organisation_name}
                    </div>
                    <form onSubmit={handleSubmit} className="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
                        <div className="mb-4">
                            <InputLabel htmlFor="name" className="block text-sm font-semibold text-gray-700">
                                Name
                            </InputLabel>
                            <TextInput
                                id="name"
                                required
                                name="name"
                                value={data.name}
                                onChange={handleChange}
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <InputError message={errors.name} className="mt-2 text-red-500 text-sm" />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="contact_group"
                                className="block text-sm font-semibold text-gray-700"
                            >
                                Contact Group
                            </InputLabel>
                            <select
                                id="contact_group"
                                required
                                name="contact_group"
                                value={data.contact_group}
                                onChange={handleChange}
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">
                                    Select Contact Group to send to
                                </option>
                                {contactGroups.map((group) => (
                                    <option key={group.uid} value={group.uid}>
                                        {group.name}
                                    </option>
                                ))}
                            </select>
                            <InputError
                                message={errors.contact_group}
                                className="mt-2 text-red-500 text-sm"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="voice"
                                className="block text-sm font-semibold text-gray-700 flex"
                            >
                                Choose Voice
                                <Tooltip text="This is the voice that will be used for voice calls in this workflow">
                                    <span className="ml-2 text-black cursor-pointer">
                                        &#x1F6C8;
                                    </span>
                                </Tooltip>
                            </InputLabel>
                            <select
                                id="voice"
                                name="voice"
                                value={data.voice}
                                onChange={(e) =>
                                    setData({
                                        ...data,
                                        voice: e.target.value,
                                    })
                                }
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">
                                    select a voice (Optional)
                                </option>
                                {voices.map((voice) => (
                                    <option
                                        key={voice.voice_id}
                                        value={voice.voice_id}
                                    >
                                        {voice.name}&nbsp;{voice.gender}
                                    </option>
                                ))}
                            </select>

                            <InputError
                                message={errors.voice}
                                className="mt-2 text-red-500 text-sm"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="agent-phone-number"
                                className=" text-sm font-semibold text-gray-700 flex"
                            >
                                Agent Phone Number
                                <Tooltip text="This is the phone number the calls will be transferred to. Enter a phone number that you have access to, prefferably your cell phone number">
                                    <span className="ml-2 text-black cursor-pointer">
                                        &#x1F6C8;
                                    </span>
                                </Tooltip>
                            </InputLabel>
                            <div className="flex">
                                <input
                                    type="text"
                                    id="agent-phone-number"
                                    name="agent-phone-number"
                                    value={data.agent_phone_number}
                                    required
                                    onChange={(e) =>
                                        setData(
                                            "agent_phone_number",
                                            e.target.value
                                        )
                                    }
                                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Enter phone number"
                                />
                            </div>
                            <InputError
                                message={errors.agent_phone_number}
                                className="mt-2 text-red-500 text-sm"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="calling_number"
                                className=" text-sm font-semibold text-gray-700 flex"
                            >
                                Calling number
                                <Tooltip text="Should be from signalwire">
                                    <span className="ml-2 text-black cursor-pointer">
                                        &#x1F6C8;
                                    </span>
                                </Tooltip>
                            </InputLabel>
                            <div className="flex">
                                <select
                                    id="calling_number"
                                    name="calling_number"
                                    required
                                    value={data.calling_number}
                                    onChange={(e) =>
                                        setData(
                                            "calling_number",
                                            e.target.value
                                        )
                                    }
                                    className="ml-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">
                                        Select a calling number
                                    </option>
                                    {calling_numbers.map((number) => (
                                        <option
                                            key={number.id}
                                            value={number.phone_number}
                                        >
                                            {number.phone_number} -{" "}
                                            {number.provider}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <InputError
                                message={errors.calling_number}
                                className="mt-2 text-red-500 text-sm"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="texting_number"
                                className=" text-sm font-semibold text-gray-700 flex"
                            >
                                texting number
                                <Tooltip text="Leave Empty  When Using A Number Pool">
                                    <span className="ml-2 text-black cursor-pointer">
                                        &#x1F6C8;
                                    </span>
                                </Tooltip>
                            </InputLabel>
                            <div className="flex">
                                <select
                                    id="texting_number"
                                    name="texting_number"
                                    value={data.texting_number}
                                    onChange={(e) =>
                                        setData(
                                            "texting_number",
                                            e.target.value
                                        )
                                    }
                                    className="ml-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">
                                        Select a texting number
                                    </option>
                                    {texting_numbers.map((number) => (
                                        <option
                                            key={number.id}
                                            value={number.phone_number}
                                        >
                                            {number.phone_number} -{" "}
                                            {number.provider}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <InputError
                                message={errors.calling_number}
                                className="mt-2 text-red-500 text-sm"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="texting_number"
                                className=" text-sm font-semibold text-gray-700 flex"
                            >
                                Select a pool
                            </InputLabel>
                            <div className="flex">
                                <select
                                    id="number_pool_id"
                                    name="number_pool_id"
                                    value={data.number_pool_id}
                                    onChange={(e) =>
                                        setData(
                                            "number_pool_id",
                                            e.target.value
                                        )
                                    }
                                    className="ml-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">
                                        Select a number pool
                                    </option>
                                    {numberPools.map((numberPool) => (
                                        <option
                                            key={numberPool.id}
                                            value={numberPool.id}
                                        >
                                            {numberPool.pool_name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <InputError
                                message={errors.number_pool_id}
                                className="mt-2 text-red-500 text-sm"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="generated_message"
                                className=" text-sm font-semibold text-gray-700 flex"
                            >
                                Use Generated Message as First Step?                            </InputLabel>
                            <div className="flex">
                                <select
                                    id="generated_message"
                                    name="generated_message"
                                    value={data.generated_message}
                                    onChange={(e) =>
                                        setData(
                                            "generated_message",
                                            e.target.value
                                        )
                                    }
                                    className="ml-2 mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">
                                        Select An Option
                                    </option>
                                    <option value='0'>No</option>
                                    <option value='1'>Yes</option>
                                </select>
                            </div>
                            <InputError
                                message={errors.generated_message}
                                className="mt-2 text-red-500 text-sm"
                            />
                        </div>
                        <div className="mt-6">
                            <PrimaryButton
                                type="submit"
                                disabled={processing}
                                className="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                {processing ? "Creating Workflow..." : "Create"}
                            </PrimaryButton>
                        </div>
                    </form>
                    {/* Workflows Table */}
                    <div className="mt-10">
                        <div className="flex items-center justify-between mb-2">
                            <h3 className="text-xl font-bold text-center text-gray-800">Workflows</h3>
                            <button
                                onClick={handleMassDelete}
                                disabled={selectedWorkflows.length === 0}
                                className={`ml-2 px-3 py-1 rounded text-xs font-semibold ${selectedWorkflows.length === 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-red-500 text-white hover:bg-red-600'}`}
                            >
                                Delete Selected
                            </button>
                        </div>
                        <div className="overflow-x-auto max-w-full">
                            <table className="min-w-full table-auto bg-white shadow-md rounded-lg text-sm">
                                <thead>
                                    <tr>
                                        <th className="px-2 py-1 bg-gray-100 text-center w-8 max-w-[32px]">
                                            <input
                                                type="checkbox"
                                                checked={selectAll}
                                                onChange={handleSelectAll}
                                            />
                                        </th>
                                        <th className="px-2 py-1 bg-gray-100 text-left w-12 max-w-[60px]">ID</th>
                                        <th className="px-2 py-1 bg-gray-100 text-left max-w-[120px]">
                                            <input
                                                type="text"
                                                placeholder="Search Name"
                                                value={searchName}
                                                onChange={(e) => setSearchName(e.target.value)}
                                                className="w-full p-1 border rounded text-xs"
                                            />
                                        </th>
                                        <th className="px-2 py-1 bg-gray-100 text-left hidden md:table-cell max-w-[120px]">Contact Group</th>
                                        <th className="px-2 py-1 bg-gray-100 max-w-[60px]">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {workflows.data.map((workflow) => (
                                        <tr key={workflow.id} className="hover:bg-gray-50">
                                            <td className="px-2 py-1 text-center max-w-[32px]">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedWorkflows.includes(workflow.id)}
                                                    onChange={() => handleWorkflowSelect(workflow.id)}
                                                />
                                            </td>
                                            <td className="px-2 py-1 text-gray-700 max-w-[60px] break-words whitespace-pre-wrap">{workflow.id}</td>
                                            <td className="px-2 py-1 text-gray-700 max-w-[120px] break-words whitespace-pre-wrap">{workflow.name}</td>
                                            <td className="px-2 py-1 text-gray-500 hidden md:table-cell max-w-[120px] break-words whitespace-pre-wrap">{workflow.contact_group}</td>
                                            <td className="px-1 py-1 max-w-[80px]">
                                                <div className="flex justify-center gap-1">
                                                    <button
                                                        onClick={() => handleCopyClick(workflow)}
                                                        className="p-1 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                                    >
                                                        <FontAwesomeIcon icon={faCopy} />
                                                    </button>
                                                    <button
                                                        onClick={() => handleAssignFolder(workflow)}
                                                        className="p-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                                    >
                                                        <FontAwesomeIcon icon={faFolderOpen} />
                                                    </button>
                                                    <Link
                                                        href={route("add_steps", workflow.id)}
                                                        className="p-1 bg-green-500 text-white rounded-md hover:bg-green-600"
                                                    >
                                                        <FontAwesomeIcon icon={faPen} />
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {/* Pagination Controls */}
                        <div className="flex flex-wrap justify-center items-center mt-2 gap-1">
                            {workflows.links.map((link, index) => (
                                <button
                                    key={index}
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                    className={`px-2 py-1 text-xs rounded ${link.active
                                        ? "bg-blue-500 text-white"
                                        : "bg-gray-200 text-gray-700 hover:bg-gray-300"
                                        }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                </div>
                <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                    <div className="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
                        <div className="py-2 text-xl font-bold text-gray-800">Create A New Folder</div>
                        <form
                            onSubmit={createFolder}
                            className="max-w-md mx-auto"
                        >
                            <div className="mb-4">
                                <InputLabel
                                    htmlFor="name"
                                    className="block text-sm font-semibold text-gray-700"
                                >
                                    Folder Name
                                </InputLabel>
                                <TextInput
                                    id="folder_name"
                                    required
                                    name="folder_name"
                                    value={data.folder_name}
                                    onChange={handleChange}
                                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                />

                                <InputError
                                    message={errors.folder_name}
                                    className="mt-2 text-red-500 text-sm"
                                />
                            </div>
                            <div className="mt-4">
                                <PrimaryButton
                                    type="submit"
                                    disabled={processing}
                                    className="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    {processing
                                        ? "Creating Folder..."
                                        : "Create"}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                    {/* Folders Table */}
                    <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                        <div className="overflow-x-auto max-w-full">
                            <table className="min-w-full table-auto bg-white shadow-md rounded-lg text-sm">
                                <thead>
                                    <tr>
                                        <th className="px-2 py-1 bg-gray-100 text-left w-12 max-w-[60px]">ID</th>
                                        <th className="px-2 py-1 bg-gray-100 text-left max-w-[120px]">
                                            <input
                                                type="text"
                                                placeholder="Search Name"
                                                value={searchFolderName}
                                                onChange={(e) => setSearchFolderName(e.target.value)}
                                                className="w-full p-1 border rounded text-xs"
                                            />
                                        </th>
                                        <th className="px-2 py-1 bg-gray-100 max-w-[60px]">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {folders.map((folder) => (
                                        <tr key={folder.id} className="hover:bg-gray-50">
                                            <td className="px-2 py-1 text-gray-700 max-w-[60px] break-words whitespace-pre-wrap">{folder.id}</td>
                                            <td className="px-2 py-1 text-gray-700 max-w-[120px] break-words whitespace-pre-wrap">{folder.name}</td>
                                            <td className="px-1 py-1 max-w-[60px]">
                                                <div className="flex justify-end gap-1">
                                                    <button
                                                        onClick={() => deleteFolder(folder.id)}
                                                        className="p-1 bg-red-500 text-white rounded-md hover:bg-red-600"
                                                    >
                                                        <FontAwesomeIcon icon={faTrash} />
                                                    </button>
                                                    <button
                                                        onClick={() => handleViewFolder(folder)}
                                                        className="p-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                                    >
                                                        <FontAwesomeIcon icon={faEye} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {/* Pagination Controls
                        <div className="flex flex-wrap justify-center items-center mt-2 gap-1">
                            {folders.links.map((link, index) => (
                                <button
                                    key={index}
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })}
                                    className={`px-2 py-1 text-xs rounded ${link.active
                                        ? "bg-blue-500 text-white"
                                        : "bg-gray-200 text-gray-700 hover:bg-gray-300"
                                        }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div> */}
                    </div>
                </div>
            </div>
            <CopyWorkflowPopup
                showPopup={showPopup}
                setShowPopup={setShowPopup}
                data={data}
                errors={errors}
                handleChange={handleChange}
                handleCopySubmit={handleCopySubmit}
                contactGroups={contactGroups}
            />
            <AssignFolderPopup
                showFolderPopup={showFolderPopup}
                setShowFolderPopup={setShowFolderPopup}
                data={data}
                errors={errors}
                handleChange={handleChange}
                handleAssignFolderSubmit={handleAssignFolderSubmit}
                folders={folders}
            />
            <ViewFolderPopup
                showViewFolderPopup={showViewFolderPopup}
                setShowViewFolderPopup={setShowViewFolderPopup}
                data={data}
                handleCopyClick={handleCopyClick}
                handleAssignFolder={handleAssignFolder}
            />
        </AuthenticatedLayout>
    );
}
