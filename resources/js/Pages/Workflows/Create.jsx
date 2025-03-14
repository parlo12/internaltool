import React, { useState, useEffect } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
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
    organisation
}) {
    console.log(voices);
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
        number_pool_id:""
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
        setData({
            id: workflow.id,
        });
        setShowFolderPopup(true);
    };
    const handleAssignFolderSubmit = (e) => {
        e.preventDefault();
        post("/assign-folder");
        setShowFolderPopup(false);
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
        axios
            .delete(`/delete-folder/${deletedFolderId}`, {})
            .then((response) => {
                console.log(response);
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
            <div className="container min-h-screen mx-auto">
                <div className="w-full p-2">
                    {success && (
                        <div className="bg-green-500 text-center text-white relative">
                            {success}
                        </div>
                    )}
                    {message && (
                        <div className="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-green-500 text-white p-4 rounded">
                            {message}
                        </div>
                    )}
                    {errorMessage && (
                        <div className="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-500 text-white p-4 rounded">
                            {errorMessage}
                        </div>
                    )}
                    {error && (
                        <div className="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-500 text-white p-4 rounded">
                            {error}
                        </div>
                    )}
                    <div className="text-2xl text-center">
                        Create a New Workflow for org {organisation && organisation.organisation_name}
                    </div>
                    <form onSubmit={handleSubmit} className="max-w-md mx-auto">
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="name"
                                className="block text-sm font-medium"
                            >
                                Name
                            </InputLabel>
                            <TextInput
                                id="name"
                                required
                                name="name"
                                value={data.name}
                                onChange={handleChange}
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            />
                            <InputError
                                message={errors.name}
                                className="mt-2"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="contact_group"
                                className="block text-sm font-medium"
                            >
                                Contact Group
                            </InputLabel>
                            <select
                                id="contact_group"
                                required
                                name="contact_group"
                                value={data.contact_group}
                                onChange={handleChange}
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
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
                                className="mt-2"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="voice"
                                className="block text-sm font-medium flex"
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
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
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
                                className="mt-2"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="agent-phone-number"
                                className=" text-sm font-medium flex"
                            >
                                Agent Phone Number
                                <Tooltip text="This is the phone number the calls will be transferred to. Enter a phone number that you have access to, prefferably your cell phone number">
                                    <span className="ml-2 text-black cursor-pointer">
                                        &#x1F6C8;
                                    </span>
                                </Tooltip>
                            </InputLabel>
                            <div className="flex">
                                {/* <select
                                    id="country-code"
                                    name="country-code"
                                    value={data.country_code}
                                    onChange={(e) =>
                                        setData("country_code", e.target.value)
                                    }
                                    className="ml-2 mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-1/4 shadow-sm sm:text-sm border-gray-300 rounded-md"
                                >
                                    <option value="+1">+1 (US)</option>
                                    <option value="+254">+254 (Kenya)</option>
                                </select> */}
                                <input
                                    type="text"
                                    id="agent-phone-number"
                                    name="agent-phone-number"
                                    value={data.agent_phone_number}
                                    onChange={(e) =>
                                        setData(
                                            "agent_phone_number",
                                            e.target.value
                                        )
                                    }
                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Enter phone number"
                                />
                            </div>
                            <InputError
                                message={errors.agent_phone_number}
                                className="mt-2"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="calling_number"
                                className=" text-sm font-medium flex"
                            >
                                Calling number
                                <Tooltip text="Must be from signalWire">
                                    <span className="ml-2 text-black cursor-pointer">
                                        &#x1F6C8;
                                    </span>
                                </Tooltip>
                            </InputLabel>
                            <div className="flex">
                                <select
                                    id="calling_number"
                                    name="calling_number"
                                    value={data.calling_number}
                                    onChange={(e) =>
                                        setData(
                                            "calling_number",
                                            e.target.value
                                        )
                                    }
                                    className="ml-2 mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
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
                                className="mt-2"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="texting_number"
                                className=" text-sm font-medium flex"
                            >
                                texting number
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
                                    className="ml-2 mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
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
                                className="mt-2"
                            />
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="texting_number"
                                className=" text-sm font-medium flex"
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
                                    className="ml-2 mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
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
                                className="mt-2"
                            />
                        </div>
                        <div className="mt-4">
                            <PrimaryButton
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                {processing ? "Creating Workflow..." : "Create"}
                            </PrimaryButton>
                        </div>
                    </form>

                    <div className="p-4 flex flex-col items-center justify-center">
                        <h3 className="text-2xl text-center">Workflows</h3>
                        <div className="py-8">
                            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div className="p-6 bg-white border-b border-gray-200">
                                        <div className="overflow-x-auto max-w-full">
                                            <table className="min-w-full divide-y divide-gray-200">
                                                <thead>
                                                    <tr>
                                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            ID
                                                        </th>
                                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Name
                                                        </th>
                                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Contact Group
                                                        </th>
                                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Actions
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody className="bg-white divide-y divide-gray-200">
                                                    {folders.map((folder) => (
                                                        <tr key={folder.id}>
                                                            <td className="px-6 py-1 text-sm text-gray-500">
                                                                {folder.id}
                                                            </td>
                                                            <td className="px-6 py-1  text-sm text-gray-500">
                                                                <FontAwesomeIcon icon={faFolderOpen} className="fa-xs" />
                                                            </td>
                                                            <td className="px-6 py-1  text-sm text-gray-500">
                                                                {folder.name}
                                                            </td>
                                                            <td className="px-6 py-1 whitespace-nowrap text-right text-sm font-medium">
                                                                <button onClick={() => deleteFolder(folder.id)} className={`inline-flex items-center px-4 py-1 border border-transparent`}>
                                                                    <FontAwesomeIcon icon={faTrash} className="fa-xs" />
                                                                </button>
                                                                <button onClick={() => handleViewFolder(folder)} className="inline-flex items-center px-4 py-2 border border-transparent">
                                                                    <FontAwesomeIcon icon={faEye} className="fa-xs" />
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                    {workflows.map((workflow) => (
                                                        <tr key={workflow.id}>
                                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                {workflow.id}
                                                            </td>
                                                            <td className="px-6 py-4 text-sm font-medium text-gray-900">
                                                                {workflow.name}
                                                            </td>
                                                            <td className="px-6 py-4  text-sm text-gray-500">
                                                                {workflow.contact_group}
                                                            </td>
                                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                                <button onClick={() => handleCopyClick(workflow)} className="inline-flex items-center px-4 py-2 border border-transparent hover:bg-gray-300">
                                                                    <FontAwesomeIcon icon={faCopy} className="fa-xs" />
                                                                </button>
                                                                <button onClick={() => handleAssignFolder(workflow)} className="inline-flex items-center px-4 py-2 border border-transparent hover:bg-gray-300">
                                                                    <FontAwesomeIcon icon={faFolderOpen} className="fa-xs" />
                                                                </button>
                                                                <Link href={route("add_steps", workflow.id)} className="inline-flex items-center px-4 py-2 border border-transparent hover:bg-gray-300">
                                                                    <FontAwesomeIcon icon={faPen} className="fa-xs" />
                                                                </Link>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                        <div className="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
                            <div className="py-2">Create A New Folder</div>
                            <form
                                onSubmit={createFolder}
                                className="max-w-md mx-auto"
                            >
                                <div className="mb-4">
                                    <InputLabel
                                        htmlFor="name"
                                        className="block text-sm font-medium"
                                    >
                                        Folder Name
                                    </InputLabel>
                                    <TextInput
                                        id="folder_name"
                                        required
                                        name="folder_name"
                                        value={data.folder_name}
                                        onChange={handleChange}
                                        className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    />
                                    <InputError
                                        message={errors.folder_name}
                                        className="mt-2"
                                    />
                                </div>
                                <div className="mt-4">
                                    <PrimaryButton
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        {processing
                                            ? "Creating Folder..."
                                            : "Create"}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                        <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                            <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Id
                                            </th>
                                            <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Folder Name
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {folders.map((folder) => (
                                            <tr key={folder.id}>
                                                <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                    {folder.id}
                                                </td>
                                                <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                    {folder.name}
                                                </td>
                                                <td className="px-6 py-1 whitespace-nowrap text-right text-sm font-medium">
                                                    <button
                                                        onClick={() =>
                                                            deleteFolder(
                                                                folder.id
                                                            )
                                                        }
                                                        className={`inline-flex items-center px-4 py-1 border border-transparent hover:bg-gray-300 `}
                                                    >
                                                        <FontAwesomeIcon
                                                            icon={faTrash}
                                                            className="fa-xs"
                                                        />
                                                    </button>
                                                    <button
                                                        onClick={() =>
                                                            handleViewFolder(
                                                                folder
                                                            )
                                                        }
                                                        className="inline-flex items-center px-4 py-2 border border-transparent hover:bg-gray-300"
                                                    >
                                                        <FontAwesomeIcon
                                                            icon={faEye}
                                                            className="fa-xs"
                                                        />
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
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
            </div>
        </AuthenticatedLayout>
    );
}
