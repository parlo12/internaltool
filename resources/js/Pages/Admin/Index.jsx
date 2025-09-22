import { Head, Link, router, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import TextAreaInput from "@/Components/TextAreaInput";
import InputLabel from "@/Components/InputLabel";
import React, { useState, useEffect } from "react";
import TextInput from "@/Components/TextInput";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import axios from 'axios';

import {
    faExchangeAlt,
    faEye,
    faPen,
    faTrash,
} from "@fortawesome/free-solid-svg-icons";
import ViewOrgPopup from "@/Components/ViewOrgPopup";
import ViewSendingServerPopup from "@/Components/ViewSendingServerPopup";
import ViewNumberPoolPopup from "@/Components/ViewNumberPoolPopup";
import UpdateOrgPopup from "@/Components/UpdateOrgPopup";
import UpdateSendingServerPopup from "@/Components/UpdateSendingServerPopup";
import UpdateNumberPoolPopup from "@/Components/UpdateNumberPoolPopup";
import UpdateNumberPopup from "@/Components/UpdateNumberPopup";

export default function Index({
    auth,
    success,
    error,
    users,
    spintaxes,
    numbers,
    organisations,
    sendingServers,
    organisation,
    numberPools,
    agents,
    files,
    propertyDetails
}) {
    const serverLookup = Object.fromEntries(sendingServers.data.map(server => [server.id, server.server_name]));
    const NumberPoolLookup = Object.fromEntries(numberPools.data.map(numberPool => [numberPool.id, numberPool.pool_name]));
    const [message, setMessage] = useState(null);
    const [errorMessage, setErrorMessage] = useState(null);
    const [showOrgPopup, setShowOrgPopup] = useState(false);
    const [showSendingServerPopup, setShowSendingServerPopup] = useState(false);
    const [showNumberPoolPopup, setShowNumberPoolPopup] = useState(false);
    const [showUpdateOrgPopup, setShowUpdateOrgPopup] = useState(false);
    const [showUpdateSendingServerPopup, setShowUpdateSendingServerPopup] = useState(false);
    const [showUpdateNumberPoolPopup, setShowUpdateNumberPoolPopup] = useState(false);
    const [showUpdateNumberPopup, setShowUpdateNumberPopup] = useState(false);

    const [contact, setContact] = useState(null);
    const [orgData, setOrgData] = useState({
        organisation_id: organisation.id,
        organisation_name: organisation.organisation_name,
        openAI: organisation.openAI,
        sending_email: organisation.sending_email,
        email_password: organisation.email_password,
        calling_service: organisation.calling_service,
        texting_service: organisation.texting_service,
        signalwire_texting_space_url: organisation.signalwire_texting_space_url,
        signalwire_texting_api_token: organisation.signalwire_texting_api_token,
        signalwire_texting_project_id: organisation.signalwire_texting_project_id,
        twilio_texting_auth_token: organisation.twilio_texting_auth_token,
        twilio_texting_account_sid: organisation.twilio_texting_account_sid,
        twilio_calling_account_sid: organisation.twilio_calling_account_sid,
        twilio_calling_auth_token: organisation.twilio_calling_auth_token,
        signalwire_calling_space_url: organisation.signalwire_calling_space_url,
        signalwire_calling_api_token: organisation.signalwire_calling_api_token,
        signalwire_calling_project_id: organisation.signalwire_calling_project_id,
        user_id: organisation.user_id,
        api_url: organisation.api_url,
        auth_token: organisation.auth_token,
        device_id: organisation.device_id,
    });
    const [sendingServerData, setSendingServerData] = useState({
        sending_server_id: "",
        server_name: "",
        purpose: "",
        service_provider: "",
        signalwire_space_url: "",
        signalwire_api_token: "",
        signalwire_project_id: "",
        twilio_auth_token: "",
        twilio_account_sid: "",
        user_id: organisation.user_id,
        websockets_api_url: "",
        websockets_auth_token: "",
        websockets_device_id: "",
        retell_api: "",
        retell_agent_id: "",
    });
    const [numberPoolData, setNumberPoolData] = useState({
        pool_messages: "",
        pool_name: "",
        pool_time: "",
        pool_time_units: "",
        number_pool_id: ""
    });

    const [numberData, setNumberData] = useState({
        phone_number: '',
        purpose: '',
        provider: '',
        sending_server_id: '',
        number_pool_id: '',
        redirect_to: '',
    });
    const handleSearch = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post('/contacts/search', {
                phone_number: data.phone_number,
            });

            if (response.data.status === 'success') {

                setContact(response.data.contact);
                setErrorMessage(null);
            } else {
                setContact(null);
                setErrorMessage(response.data.message);
            }
        } catch (err) {
            setContact(null);
            setError('An error occurred while searching.');
        }
    };

    const handleToggleAdmin = (userId) => {
        router.post(route("users.toggle-admin", { user: userId }), {
            preserveScroll: true,
            onSuccess: () => {
            },
        });
    };
    const handleFormChange = (e) => {
        setWorkflowData({ ...workflowData, [e.target.name]: e.target.value });
    };
    const { data, setData, post, errors, processing, reset } = useForm({
        content: "",
        phone_number: "",
        phone_number_provider: "",
        number_purpose: "",
        purpose: "",
        calling_service: "",
        signalwire_space_url: "",
        signalwire_api_token: "",
        signalwire_project_id: "",
        twilio_auth_token: "",
        twilio_account_sid: "",
        texting_service: "",
        organisation_name: "",
        org_id: "",
        sending_server_id: "",
        api_key: "",
        user_id: "",
        openAI: "",
        email_password: "",
        sending_email: "",
        websockets_api_url: "",
        websockets_auth_token: "",
        websockets_device_id: "",
        server_name: "",
        service_provider: "",
        pool_messages: "",
        pool_name: "",
        pool_time: "",
        pool_time_units: "",
        number_pool_id: "",
        files: null,
        upa: propertyDetails?.data?.[0]?.upa ?? "",
        sca: propertyDetails?.data?.[0]?.sca ?? "",
        downpayment: propertyDetails?.data?.[0]?.downpayment ?? "",
        purchase_price: propertyDetails?.data?.[0]?.purchase_price ?? "",
        plc: propertyDetails?.data?.[0]?.plc ?? "",
        agreed_net_proceeds: propertyDetails?.data?.[0]?.agreed_net_proceeds ?? "",
        remaining_amount_after_ANP: propertyDetails?.data?.[0]?.remaining_amount_after_ANP ?? "",
        monthly_amount: propertyDetails?.data?.[0]?.monthly_amount ?? "",
    });
    const onSubmit = (e) => {
        e.preventDefault();
        post("/store-spintax");
        reset();
    };

    const storeFiles = async (e) => {
        e.preventDefault();

        if (!data.files || data.files.length === 0) {
            alert('Please select at least one file to upload.');
            return;
        }

        const formData = new FormData();
        for (let i = 0; i < data.files.length; i++) {
            formData.append('files[]', data.files[i]);
        }

        try {
            const response = await axios.post('/store-files', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            // Assuming response.data.files is an array of uploaded file info
            //setUploadedFiles((prev) => [...prev, ...response.data.files]);
            setData({ ...data, files: null }); // reset input
            location.reload();
        } catch (error) {
            console.error('Upload failed:', error);
            alert('Failed to upload files.');
        }
    };

    const deleteFile = async (file_id) => {
        try {
            await axios.delete(`/delete-file/${file_id}`);
            location.reload(); // Reload the page to reflect changes
            preserveScroll();
        } catch (error) {
            console.error('Failed to delete file:', error);
        }
    };

    const submitOrganisation = (e) => {
        e.preventDefault();
        post("/store-organisation");
        reset();
    };
    const submitServer = (e) => {
        e.preventDefault();
        post("/store-server");
        reset();
    };
    const deleteUser = (user) => {

        const confirmDelete = window.confirm(`Are you sure you want to delete this user: ${user.name}? This cannot be undone`);

        if (confirmDelete) {
            // Proceed with the deletion
            window.location.href = route('delete-user', user.id);
        }
    };
    const submitNumber = (e) => {
        e.preventDefault();
        post("/store-number");
        reset();
    };
    const submitNumberPool = (e) => {
        e.preventDefault();
        post("/store-number-pool");
        reset();
    };
    const deleteSpintax = (deletedSpintaxId) => {
        axios
            .delete(`/delete-spintax/${deletedSpintaxId}`, {})
            .then((response) => {


                setMessage(`Spintax ${response.content} deleted successfully`);
                location.reload();
            })
            .catch((error) => {
                setErrorMessage("Error Deleting Spintax");
                console.error(
                    "Error Deleting Spintax",
                    error.response?.data || error.message
                );
            });
    };
    const deleteNumber = (deletedNumberId) => {
        axios
            .delete(`/delete-number/${deletedNumberId}`, {})
            .then((response) => {


                setMessage(`Number ${response.content} deleted successfully`);
                location.reload();
            })
            .catch((error) => {
                setErrorMessage("Error Deleting Number");
                console.error(
                    "Error Deleting Number",
                    error.response?.data || error.message
                );
            });
    };
    const deleteNumberPool = (deletedPoolId) => {
        axios
            .delete(`/delete-number-pool/${deletedPoolId}`, {})
            .then((response) => {


                setMessage(`Number pool deleted successfully`);
                location.reload();
            })
            .catch((error) => {
                setErrorMessage("Error Deleting Number Pool");
                console.error(
                    "Error Deleting Number Pool",
                    error.response?.data || error.message
                );
            });
    };
    const handleViewOrg = (org) => {
        setData({
            org_id: org.id,
        });
        setShowOrgPopup(true);
    };
    const handleViewSendingServer = (sendingServer) => {
        setData({
            sending_server_id: sendingServer.id,
        });
        setShowSendingServerPopup(true);
    }
    const handleUpdateOrg = (org) => {
        setData({
            org_id: org.id,
        });
        setShowUpdateOrgPopup(true);
    };
    const handleUpdateSendingServer = (sendingServer) => {
        setSendingServerData(prevData => ({
            ...prevData,  // Spread existing data
            sending_server_id: sendingServer.id  // Update specific field
        }));

        setShowUpdateSendingServerPopup(true);
    };
    const handleViewNumberPool = (numberPool) => {
        setData({
            number_pool_id: numberPool.id,
        });
        setShowNumberPoolPopup(true);
    }
    const handleUpdateNumberPool = (numberPool) => {
        setNumberPoolData(prevData => ({
            ...prevData,  // Spread existing data
            number_pool_id: numberPool.id  // Update specific field
        }));

        setShowUpdateNumberPoolPopup(true);
    };
    const handleUpdateNumber = (number) => {
        setNumberData(prevData => ({
            ...prevData,  // Spread existing data
            number_id: number.id  // Update specific field
        }));

        setShowUpdateNumberPopup(true);
    };
    const submitOrganisationUpdate = async (e) => {
        e.preventDefault();
        try {

            const response = await axios.post('/update-organisation', orgData);
            setMessage(`Org update  successfull`);
            setShowUpdateOrgPopup(false)
            setTimeout(() => { window.location.reload() }, 2000);

        } catch (error) {
            setErrorMessage("Error switching to org");
            setShowUpdateOrgPopup(false)
            console.error('Error updating org', error);
        }
    };
    const submitServerUpdate = async (e) => {
        e.preventDefault();
        try {

            const response = await axios.post('/update-server', sendingServerData);
            setMessage(`Server update  successfull`);
            setShowUpdateSendingServerPopup(false)
            setTimeout(() => { window.location.reload() }, 2000);

        } catch (error) {
            setErrorMessage("Error updating server");
            setShowUpdateSendingServerPopup(false)
            console.error('Error updating org', error);
        }
    };
    const submitNumberPoolUpdate = async (e) => {
        e.preventDefault();
        try {

            const response = await axios.post('/update-number-pool', numberPoolData);
            setMessage(`Number Pool update  successfull`);
            setShowUpdateNumberPoolPopup(false)
            setTimeout(() => { window.location.reload() }, 2000);

        } catch (error) {
            setErrorMessage("Error updating number pool");
            setShowUpdateNumberPoolPopup(false)
            console.error('Error updating org', error);
        }
    };
    const submitNumberUpdate = async (e) => {
        e.preventDefault();
        try {

            const response = await axios.post('/update-number', numberData);
            setMessage(`Number update  successfull`);
            setShowUpdateNumberPopup(false)
            setTimeout(() => { window.location.reload() }, 2000);

        } catch (error) {
            setErrorMessage("Error updating number pool");
            setShowUpdateNumberPopup(false)
            console.error('Error updating number', error);
        }
    };
    const switchOrg = (orgId) => {
        axios
            .get(`/switch-organisation/${orgId}`)
            .then((response) => {
                setMessage(`Switch to org ${orgId} was successfull`);


                window.location.reload();
            })
            .catch((error) => {
                setErrorMessage("Error switching to org");
                console.error("There was a problem with the request:", error);
            });
    };

    const [apiKeys, setApiKeys] = useState({}); // Track API keys by user ID

    const handleApiKeyChange = (userId, value) => {
        setApiKeys((prevState) => ({
            ...prevState,
            [userId]: value,
        }));
    };

    const submitApiKey = (userId) => {
        const apiKey = apiKeys[userId];
        if (apiKey) {
            // handleKeyPress(apiKey, userId);

            axios
                .post("/submit-api-key", {
                    api_key: apiKey,
                    user_id: userId,
                })
                .then((response) => {



                    setMessage(`Godspeedoffers api key updated successfully`);
                    setTimeout(() => { window.location.reload() }, 2000);
                })
                .catch((error) => {
                    console.error("Error submitting API Key:", error);
                    setErrorMessage("Error updating Godspeedoffers api key");
                    setTimeout(() => { window.location.reload() }, 2000);
                });
        }
    };
    const storePropertyDetails = (e) => {
        e.preventDefault();
        axios.post("/property-details", data)
            .then((response) => {
                setData({ upa: "", sca: "", downpayment: "", purchase_price: "", agreed_net_proceeds: '', remaining_amount_after_ANP: "" });
                location.reload(); // Reload the page to reflect changes
                preserveScroll();
            })
            .catch(err => console.error(err));
    };
    const handleUpdateOrganisation = async (userId, organisationId) => {
        if (!organisationId) return;

        try {
            await axios.post("/update-user-organisation", {
                user_id: userId,
                organisation_id: organisationId,
            });
            setMessage(
                `User ${userId} assigned to org with id ${organisationId} successfully`
            );
            window.location.reload();
            // Handle success (e.g., display a success message or update UI)
        } catch (error) {
            // Handle error (e.g., display an error message)
            setErrorMessage("Error assigning user to org");
            console.error("Failed to update organisation:", error);
        }
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
            <Head title="Admin" />
            <div className="py-12 bg-gray-100 min-h-screen">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-2xl text-center font-semibold text-gray-800 mb-6">
                        You are now managing org:{" "}
                        <span className="text-indigo-600">{organisation?.organisation_name}</span>
                    </div>
                    <div className="bg-white shadow-md rounded-lg p-6">
                        {error && (
                            <div className="bg-red-100 text-red-700 p-4 rounded mb-4">
                                {error}
                            </div>
                        )}
                        {success && (
                            <div className="bg-green-100 text-green-700 p-4 rounded mb-4">
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
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Admin Status
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Organisation
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Godspeed API Key
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            workflow API Key
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {users.data.map((user) => (
                                        <tr key={user.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {user.name}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {user.email}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {user.is_admin ? "Admin" : "User"}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <select
                                                    value={user.organisation_id}
                                                    onChange={(e) =>
                                                        handleUpdateOrganisation(user.id, e.target.value)
                                                    }
                                                    className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                >
                                                    <option value="">
                                                        {organisations.data.find(
                                                            (org) => org.id === user.organisation_id
                                                        )?.organisation_name || "Select Organisation"}
                                                    </option>
                                                    {organisations.data.map((org) => (
                                                        <option key={org.id} value={org.id}>
                                                            {org.organisation_name}
                                                        </option>
                                                    ))}
                                                </select>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div className="w-48 overflow-auto">{user.godspeedoffers_api}</div>
                                                <input
                                                    type="text"
                                                    value={apiKeys[user.id] || ''}
                                                    onChange={(e) =>
                                                        handleApiKeyChange(user.id, e.target.value)
                                                    }
                                                    onKeyPress={(e) => {
                                                        if (e.key === "Enter") submitApiKey(user.id);
                                                    }}
                                                    placeholder="Enter API Key"
                                                    className="mt-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                />
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {user.api_key}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    onClick={() => handleToggleAdmin(user.id)}
                                                    className={`inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white ${user.is_admin ? "bg-red-500 hover:bg-red-600" : "bg-blue-500 hover:bg-blue-600"
                                                        } focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500`}
                                                >
                                                    {user.is_admin ? "Dismiss" : "Admit"}
                                                </button>
                                                <button
                                                    onClick={() => deleteUser(user)}
                                                    className="ml-2 inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Pagination links={users.meta.links} />
                    </div>
                </div>
            </div>
            <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="max-w-md mx-auto mt-10">
                        <h1 className="text-2xl font-bold mb-4">
                            Add a spintax
                        </h1>
                        <form onSubmit={onSubmit} className="space-y-4">
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Spintax
                                </InputLabel>
                                <TextAreaInput
                                    name="spintaxMessage"
                                    value={data.content}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            content: e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter Spintax message E.G. {How are you|Hi|Hello}"
                                    rows="4"
                                    required
                                ></TextAreaInput>
                            </div>
                            <div>
                                <PrimaryButton
                                    type="submit"
                                    className="w-36 text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Add spintax
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Content
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50"></th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {spintaxes.data.map((spintax) => (
                                    <tr key={spintax.id}>
                                        <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {spintax.content}
                                        </td>
                                        <td className="px-6 py-1 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                onClick={() =>
                                                    deleteSpintax(
                                                        spintax.id
                                                    )
                                                }
                                                className={`inline-flex items-center px-4 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <Pagination links={users.meta.links} />
                    </div>
                </div>
            </div>
            <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                {/* Upload Form */}
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="max-w-md mx-auto mt-10">
                        <h1 className="text-2xl font-bold mb-4">Upload Template Files for emails</h1>
                        <form onSubmit={storeFiles} className="space-y-4">
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Select Files
                                </InputLabel>
                                <input
                                    type="file"
                                    multiple
                                    accept=".doc,.docx"
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            files: e.target.files,
                                        })
                                    }
                                    className="mt-1 block w-full text-sm text-gray-700 file:py-2 file:px-4 file:border file:rounded-md file:border-gray-300 file:bg-white file:text-sm file:font-medium"
                                />

                            </div>

                            <div>
                                <PrimaryButton
                                    type="submit"
                                    className="w-36 text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Upload
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                {/* Uploaded Files Table */}
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        File Name
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {files.data.map((file, index) => (
                                    <tr key={index}>
                                        <td className="px-6 py-2 text-sm text-gray-700">
                                            {file.name}
                                        </td>
                                        <td className="px-6 py-2 text-sm text-right">
                                            <button
                                                onClick={() => deleteFile(file.id)}
                                                className="inline-flex items-center px-4 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                {/* Form */}
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="max-w-md mx-auto mt-5">
                        <h1 className="text-2xl font-bold mb-4">Add Property Details</h1>
                        <form onSubmit={storePropertyDetails} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">UPA(%)</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="1"
                                    value={data.upa}
                                    onChange={(e) => setData({ ...data, upa: e.target.value })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md p-2"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700">SCA(%)</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="1"
                                    value={data.sca}
                                    onChange={(e) => setData({ ...data, sca: e.target.value })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md p-2"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700">Downpayment(%)</label>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    max="100"
                                    value={data.downpayment}
                                    onChange={(e) => setData({ ...data, downpayment: e.target.value })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md p-2"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">PLC(%)</label>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    max="100"
                                    value={data.plc}
                                    onChange={(e) => setData({ ...data, plc: e.target.value })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md p-2"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Purchase Price(%)</label>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    max="100"
                                    value={data.purchase_price}
                                    onChange={(e) => setData({ ...data, purchase_price: e.target.value })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md p-2"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Agreed Net Proceeds(%)</label>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    max="100"
                                    value={data.agreed_net_proceeds}
                                    onChange={(e) => setData({ ...data, agreed_net_proceeds: e.target.value })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md p-2"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Remaining Amount after ANP(%)</label>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    max="100"
                                    value={data.remaining_amount_after_ANP}
                                    onChange={(e) => setData({ ...data, remaining_amount_after_ANP: e.target.value })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md p-2"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Monthly Amount($)</label>
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    value={data.monthly_amount}
                                    onChange={(e) => setData({ ...data, monthly_amount: e.target.value })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md p-2"
                                />
                            </div>
                            <div>
                                <button
                                    type="submit"
                                    className="w-36 text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700"
                                >
                                    Save Property Details
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {/* Table */}
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    {propertyDetails?.data?.length > 0 ? (
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {/* Card Header */}
                            <h2 className="col-span-2 text-lg font-semibold text-gray-800 border-b pb-2 mb-2">
                                Property Details
                            </h2>

                            {/* Field Item */}
                            <div className="flex flex-col">
                                <span className="text-xs text-gray-500 uppercase">UPA (%)</span>
                                <span className="text-base font-medium text-gray-800">
                                    {propertyDetails.data[0].upa}
                                </span>
                            </div>

                            <div className="flex flex-col">
                                <span className="text-xs text-gray-500 uppercase">SCA (%)</span>
                                <span className="text-base font-medium text-gray-800">
                                    {propertyDetails.data[0].sca}
                                </span>
                            </div>

                            <div className="flex flex-col">
                                <span className="text-xs text-gray-500 uppercase">PLC (%)</span>
                                <span className="text-base font-medium text-gray-800">
                                    {propertyDetails.data[0].plc}
                                </span>
                            </div>

                            <div className="flex flex-col">
                                <span className="text-xs text-gray-500 uppercase">Downpayment (%)</span>
                                <span className="text-base font-medium text-gray-800">
                                    {propertyDetails.data[0].downpayment}
                                </span>
                            </div>

                            <div className="flex flex-col">
                                <span className="text-xs text-gray-500 uppercase">Purchase Price (%)</span>
                                <span className="text-base font-medium text-gray-800">
                                    {propertyDetails.data[0].purchase_price}
                                </span>
                            </div>

                            <div className="flex flex-col">
                                <span className="text-xs text-gray-500 uppercase">Agreed Net Proceeds (%)</span>
                                <span className="text-base font-medium text-gray-800">
                                    {propertyDetails.data[0].agreed_net_proceeds}
                                </span>
                            </div>

                            <div className="flex flex-col">
                                <span className="text-xs text-gray-500 uppercase">
                                    Remaining Amount After ANP (%)
                                </span>
                                <span className="text-base font-medium text-gray-800">
                                    {propertyDetails.data[0].remaining_amount_after_ANP}
                                </span>
                            </div>
                            <div className="flex flex-col">
                                <span className="text-xs text-gray-500 uppercase">
                                    Monthly Amount ($)
                                </span>
                                <span className="text-base font-medium text-gray-800">
                                    {propertyDetails.data[0].monthly_amount}
                                </span>
                            </div>
                        </div>
                    ) : (
                        <p className="text-gray-500 text-sm">No property details available.</p>
                    )}
                    {/* Display the tags here */}
                    <div className="mt-8">
                        <h3 className="text-lg font-bold text-gray-800 mb-2 tracking-wide border-b border-gray-300 pb-1">Available Template Tags</h3>
                        <div className="flex flex-wrap gap-2 mt-2">
                            {[
                                'agreement_date',
                                'AGP',
                                'RMA',
                                'property_address',
                                'contact_name',
                                'EMD',
                                'downpayment',
                                'SCA',
                                'UPA',
                                'PLC',
                                'purchase_price',
                                'monthly_amount',
                                'baloon_payment',
                                'SFA',
                                'date',
                                'closing_day',
                                'phone',
                                'contact_name',
                                'zipcode',
                                'city',
                                'state',
                                'address',
                                'offer',
                                'email',
                                'age',
                                'gender',
                                'lead_score',
                                'agent',
                                'novation',
                                'creative_price',
                                'monthly',
                                'downpayment',
                                'generated_message',
                                'earnest_money_deposit',
                                'list_price'
                            ].map(tag => (
                                <span key={tag} className="inline-block bg-gray-100 border border-gray-300 text-gray-800 text-xs font-mono px-2 py-1 rounded">
                                    $&#123;{tag}&#125;
                                </span>
                            ))}
                        </div>
                    </div>
                </div>


            </div>

            <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                <div className="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="max-w-md mx-auto mt-10">
                        <h1 className="text-2xl font-bold mb-4">
                            Add a phone number
                        </h1>
                        <form
                            onSubmit={submitNumber}
                            className="space-y-4"
                        >
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Phone Number
                                </InputLabel>
                                <TextInput
                                    name="phoneNumber"
                                    value={data.phone_number}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            phone_number:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter phone number"
                                    required
                                ></TextInput>
                            </div>
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Redirect to this Number
                                </InputLabel>
                                <TextInput
                                    name="redirect_to"
                                    value={data.redirect_to}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            redirect_to:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter phone number to direct to"
                                    required
                                ></TextInput>
                            </div>
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Phone Number Provider
                                </InputLabel>
                                <select
                                    name="phoneNumberProvider"
                                    value={data.phone_number_provider}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            phone_number_provider:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required
                                >
                                    <option value="">
                                        Select provider
                                    </option>
                                    <option value="twilio">
                                        Twilio
                                    </option>
                                    <option value="signalwire">
                                        SignalWire
                                    </option>
                                    <option value="websockets-api">
                                        Websockets-api
                                    </option>
                                    <option value="retell">
                                        Retell
                                    </option>
                                </select>
                            </div>

                            {/* cccccccccccccccccccccccccccccc */}
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Choose Server
                                </InputLabel>
                                <select
                                    name="sending_server_id"
                                    value={data.sending_server_id}
                                    onChange={(e) => setData({ ...data, sending_server_id: e.target.value })}
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required
                                >
                                    <option value="">Select Sending Server</option>
                                    {sendingServers.data.map((server) => (
                                        <option key={server.id} value={server.id}>
                                            {server.server_name} {'-'} {server.service_provider}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Calling or Texting
                                </InputLabel>
                                <select
                                    name="callingOrTexting"
                                    value={data.number_purpose}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            number_purpose:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required
                                >
                                    <option value="">
                                        Select purpose
                                    </option>
                                    <option value="calling">
                                        Calling
                                    </option>
                                    <option value="texting">
                                        Texting
                                    </option>
                                </select>
                            </div>
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Assign To Pool
                                </InputLabel>
                                <select
                                    name="number_pool_id"
                                    value={data.number_pool_id}
                                    onChange={(e) => setData({ ...data, number_pool_id: e.target.value })}
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                >
                                    <option value="">Add To A Pool</option>
                                    {numberPools.data.map((numberPool) => (
                                        <option key={numberPool.id} value={numberPool.id}>
                                            {numberPool.pool_name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <PrimaryButton
                                    type="submit"
                                    className="w-36 text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Add number
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Number
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Redirect To
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Purpose
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sending Server
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Number Pool
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50">
                                        Provider
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {numbers.data.map((number) => (
                                    <tr key={number.id}>
                                        <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {number.phone_number}
                                        </td>
                                        <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {number.redirect_to}
                                        </td>
                                        <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {number.purpose}
                                        </td>
                                        <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {serverLookup[number.sending_server_id] || 'N/A'}
                                        </td>
                                        <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {NumberPoolLookup[number.number_pool_id] || 'N/A'}
                                        </td>
                                        <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {number.provider}
                                        </td>
                                        <td className="px-6 py-1 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                onClick={() =>
                                                    deleteNumber(
                                                        number.id
                                                    )
                                                }
                                                className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                            >
                                                <FontAwesomeIcon
                                                    icon={
                                                        faTrash
                                                    }
                                                    className="fa-xs"
                                                />
                                            </button>
                                            <button
                                                onClick={() =>
                                                    handleUpdateNumber(
                                                        number
                                                    )
                                                }
                                                className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 ml-2  focus:ring-blue-500`}
                                            >
                                                <FontAwesomeIcon
                                                    icon={
                                                        faPen
                                                    }
                                                    className="fa-xs"
                                                />
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <Pagination links={users.meta.links} />
                    </div>
                </div>
            </div>
            {/* ****************************** */}
            <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                <div className="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="max-w-md mx-auto mt-10">
                        <h1 className="text-2xl font-bold mb-4">
                            Create A Number Pool
                        </h1>
                        <form
                            onSubmit={submitNumberPool}
                            className="space-y-4"
                        >
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Pool Name
                                </InputLabel>
                                <TextInput
                                    name="pool_name"
                                    value={data.pool_name}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            pool_name:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter Pool Name"
                                    required
                                ></TextInput>
                            </div>
                            <div className="flex items-center mb-4">
                                <div className="mr-2 w-2/3">
                                    <InputLabel
                                        forInput="pool_messages"
                                        value="Send"
                                    />
                                    <input
                                        type="number"
                                        min="1" // Enforce minimum wait time limit
                                        name="pool_messages"
                                        value={data.pool_messages}
                                        required
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                pool_messages:
                                                    e.target.value,
                                            })} className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                    />
                                </div>
                                <div className="mr-2 w-2/3">
                                    <InputLabel
                                        forInput="pool"
                                        value="Every"
                                    />
                                    <input
                                        type="number"
                                        min="1" // Enforce minimum wait time limit
                                        name="pool_time"
                                        value={data.pool_time}
                                        required
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                pool_time:
                                                    e.target.value,
                                            })}
                                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                    />
                                </div>
                                <div>
                                    <InputLabel
                                        forInput="pool_time_units"
                                        value="Units"
                                    />
                                    <select
                                        name="pool_time_units"
                                        required
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                pool_time_units:
                                                    e.target.value,
                                            })}
                                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                        value={data.pool_time_units}
                                    >
                                        <option value="">Select</option>
                                        <option value="minutes">Minutes</option>
                                        <option value="hours">Hours</option>
                                        <option value="days">Days</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <PrimaryButton
                                    type="submit"
                                    className=" text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Create Numbers Pool
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pool Name
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Instructions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {numberPools.data.map((numberPool) => (
                                    <tr key={numberPool.id}>
                                        <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {numberPool.pool_name}
                                        </td>
                                        <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {"send "}{numberPool.pool_messages}{" Message(s) Every "}{numberPool.pool_time}{' '}{numberPool.pool_time_units}
                                        </td>
                                        <td className="px-6 py-1 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                onClick={() =>
                                                    handleUpdateNumberPool(
                                                        numberPool
                                                    )
                                                }
                                                className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-black hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                            >
                                                <FontAwesomeIcon
                                                    icon={faPen}
                                                    className="fa-xs"
                                                />
                                            </button>
                                            <button
                                                onClick={() =>
                                                    handleViewNumberPool(
                                                        numberPool
                                                    )
                                                }
                                                className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-black hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                            >
                                                <FontAwesomeIcon
                                                    icon={faEye}
                                                    className="fa-xs"
                                                />
                                            </button>
                                            <button
                                                onClick={() =>
                                                    deleteNumberPool(
                                                        numberPool.id
                                                    )
                                                }
                                                className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-black hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                            >
                                                <FontAwesomeIcon
                                                    icon={
                                                        faTrash
                                                    }
                                                    className="fa-xs"
                                                />
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <Pagination links={users.meta.links} />
                    </div>
                </div>
            </div>
            {/* ************************************* */}
            <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                <div className="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="max-w-md mx-auto mt-10">
                        <h1 className="text-2xl font-bold mb-4">
                            Add an Organisation
                        </h1>
                        <form
                            onSubmit={submitOrganisation}
                            className="space-y-4"
                        >
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Organisation Name
                                </InputLabel>
                                <TextInput
                                    name="organisationName"
                                    value={data.organisation_name}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            organisation_name:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter organisation name"
                                    required
                                ></TextInput>
                            </div>
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Open AI Key
                                </InputLabel>
                                <TextInput
                                    name="openAI"
                                    value={data.openAI}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            openAI:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter openAI Key"
                                    required
                                ></TextInput>
                            </div>
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Sending Email
                                </InputLabel>
                                <TextInput
                                    name="sending_email"
                                    value={data.sending_email}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            sending_email:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter sending Email"
                                    required
                                ></TextInput>
                            </div>
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Email Password
                                </InputLabel>
                                <TextInput
                                    name="email_password"
                                    value={data.email_password}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            email_password:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter Email Password"
                                    required
                                ></TextInput>
                            </div>
                            <div>
                                <PrimaryButton
                                    type="submit"
                                    className="w-36 text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Add Organisation
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        organisation_name
                                    </th>

                                    <th className="px-6 py-3 bg-gray-50">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {organisations.data.map(
                                    (organisation) => (
                                        <tr key={organisation.id}>
                                            <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                {
                                                    organisation.organisation_name
                                                }
                                            </td>
                                            <td className="px-6 py-1 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    onClick={() =>
                                                        handleUpdateOrg(
                                                            organisation
                                                        )
                                                    }
                                                    className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-black hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                                >
                                                    <FontAwesomeIcon
                                                        icon={faPen}
                                                        className="fa-xs"
                                                    />
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        handleViewOrg(
                                                            organisation
                                                        )
                                                    }
                                                    className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-black hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                                >
                                                    <FontAwesomeIcon
                                                        icon={faEye}
                                                        className="fa-xs"
                                                    />
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        switchOrg(
                                                            organisation.id
                                                        )
                                                    }
                                                    className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-black hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                                >
                                                    <FontAwesomeIcon
                                                        icon={
                                                            faExchangeAlt
                                                        }
                                                        className="fa-xs"
                                                    />
                                                </button>
                                            </td>
                                        </tr>
                                    )
                                )}
                            </tbody>
                        </table>
                        <Pagination links={users.meta.links} />
                    </div>
                </div>
            </div>

            <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                <div className="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="max-w-md mx-auto mt-10">
                        <h1 className="text-2xl font-bold mb-4">
                            Add A Sending Server
                        </h1>
                        <form
                            onSubmit={submitServer}
                            className="space-y-4"
                        >
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Choose A Server and Enter Details
                                </InputLabel>
                                <div className="space-x-4">
                                    <label className="inline-flex items-center">
                                        <input
                                            type="radio"
                                            name="service_provider"
                                            value="twilio"
                                            checked={
                                                data.service_provider ===
                                                "twilio"
                                            }
                                            onChange={(e) => {
                                                setData({
                                                    ...data,
                                                    service_provider:
                                                        e.target.value,
                                                    // Clear SignalWire texting fields
                                                    signalwire_project_id:
                                                        "",
                                                    signalwire_api_token:
                                                        "",
                                                    signalwire_space_url:
                                                        "",
                                                    websockets_api_url: "",
                                                    websockets_auth_token: "",
                                                    retell_api: "",
                                                    retell_agent_id: "",
                                                    server_name: ""
                                                });
                                            }}
                                            className="form-radio"
                                            required
                                        />
                                        <span className="ml-2">
                                            Twilio
                                        </span>
                                    </label>
                                    <label className="inline-flex items-center">
                                        <input
                                            type="radio"
                                            name="service_provider"
                                            value="signalwire"
                                            checked={
                                                data.service_provider ===
                                                "signalwire"
                                            }
                                            onChange={(e) => {
                                                setData({
                                                    ...data,
                                                    service_provider:
                                                        e.target.value,
                                                    // Clear Twilio texting fields
                                                    twilio_account_sid:
                                                        "",
                                                    twilio_auth_token:
                                                        "",
                                                    websockets_api_url: "",
                                                    websockets_auth_token: "",
                                                    retell_api: "",
                                                    retell_agent_id: "",
                                                    server_name: ""
                                                });
                                            }}
                                            className="form-radio"
                                            required
                                        />
                                        <span className="ml-2">
                                            SignalWire
                                        </span>
                                    </label>
                                    <label className="inline-flex items-center">
                                        <input
                                            type="radio"
                                            name="service_provider"
                                            value="websockets-api"
                                            checked={
                                                data.service_provider ===
                                                "websockets-api"
                                            }
                                            onChange={(e) => {
                                                setData({
                                                    ...data,
                                                    service_provider:
                                                        e.target.value,
                                                    // Clear Twilio texting fields
                                                    twilio_account_sid:
                                                        "",
                                                    twilio_auth_token:
                                                        "",
                                                    signalwire_project_id:
                                                        "",
                                                    signalwire_api_token:
                                                        "",
                                                    signalwire_space_url:
                                                        "",
                                                    retell_api: "",
                                                    retell_agent_id: "",
                                                    server_name: ""
                                                });
                                            }}
                                            className="form-radio"
                                            required
                                        />
                                        <span className="ml-2">
                                            Websockets API
                                        </span>
                                    </label>
                                    <label className="inline-flex items-center">
                                        <input
                                            type="radio"
                                            name="service_provider"
                                            value="retell"
                                            checked={
                                                data.service_provider ===
                                                "retell"
                                            }
                                            onChange={(e) => {
                                                setData({
                                                    ...data,
                                                    service_provider:
                                                        e.target.value,
                                                    // Clear Twilio texting fields
                                                    twilio_account_sid:
                                                        "",
                                                    twilio_auth_token:
                                                        "",
                                                    signalwire_project_id:
                                                        "",
                                                    signalwire_api_token:
                                                        "",
                                                    signalwire_space_url:
                                                        "",
                                                    server_name: "",
                                                    websockets_api_url: "",
                                                    websockets_auth_token: ""
                                                });
                                            }}
                                            className="form-radio"
                                            required
                                        />
                                        <span className="ml-2">
                                            Retell
                                        </span>
                                    </label>
                                </div>
                            </div>

                            {data.service_provider === "twilio" && (
                                <>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Server Name
                                        </InputLabel>
                                        <TextInput
                                            name="server_name"
                                            value={
                                                data.server_name
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    server_name:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter Server Name."
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Twilio Account SID
                                        </InputLabel>
                                        <TextInput
                                            name="twilioAccountSid"
                                            value={
                                                data.twilio_account_sid
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    twilio_account_sid:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter Twilio Account SID"
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Twilio Auth Token
                                        </InputLabel>
                                        <TextInput
                                            name="twilioAuthToken"
                                            value={
                                                data.twilio_auth_token
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    twilio_auth_token:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter Twilio Auth Token"
                                            required
                                        ></TextInput>
                                        <div>
                                            <InputLabel className="block text-sm font-medium text-gray-700">
                                                Purpose
                                            </InputLabel>
                                            <select
                                                name="purpose"
                                                value={data.purpose}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        purpose:
                                                            e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                required
                                            >
                                                <option value="">
                                                    Select purpose
                                                </option>
                                                <option value="texting">
                                                    Texting
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </>
                            )}

                            {data.service_provider === "signalwire" && (
                                <>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Server Name
                                        </InputLabel>
                                        <TextInput
                                            name="server_name"
                                            value={
                                                data.server_name
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    server_name:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter Server Name."
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            SignalWire Project ID
                                        </InputLabel>
                                        <TextInput
                                            name="signalwireProjectId"
                                            value={
                                                data.signalwire_project_id
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    signalwire_project_id:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter SignalWire Project ID"
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            SignalWire API Token
                                        </InputLabel>
                                        <TextInput
                                            name="signalwireApiToken"
                                            value={
                                                data.signalwire_api_token
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    signalwire_api_token:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter SignalWire API Token"
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            SignalWire Space URL
                                        </InputLabel>
                                        <TextInput
                                            name="signalwireTextingSpaceUrl"
                                            value={
                                                data.signalwire_space_url
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    signalwire_space_url:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter SignalWire Space URL"
                                            required
                                        ></TextInput>
                                        <div>
                                            <InputLabel className="block text-sm font-medium text-gray-700">
                                                Calling or Texting
                                            </InputLabel>
                                            <select
                                                name="callingOrTexting"
                                                value={data.purpose}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        purpose:
                                                            e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                required
                                            >
                                                <option value="">
                                                    Select purpose
                                                </option>
                                                <option value="texting">
                                                    Texting
                                                </option>
                                                <option value="calling">
                                                    Calling
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </>
                            )}
                            {data.service_provider === "websockets-api" && (
                                <>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Server Name
                                        </InputLabel>
                                        <TextInput
                                            name="server_name"
                                            value={
                                                data.server_name
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    server_name:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter Server Name."
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            websockets Device ID
                                        </InputLabel>
                                        <TextInput
                                            name="websockets_device_id"
                                            value={
                                                data.websockets_device_id
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    websockets_device_id:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter Websockets Device ID"
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Websockets Auth Token
                                        </InputLabel>
                                        <TextInput
                                            name="websockets_auth_token"
                                            value={
                                                data.websockets_auth_token
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    websockets_auth_token:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="websockets Auth Token"
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Websockets Api Url
                                        </InputLabel>
                                        <TextInput
                                            name="websockets_api_url"
                                            value={
                                                data.websockets_api_url
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    websockets_api_url:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Websockets API URL"
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Calling or Texting
                                        </InputLabel>
                                        <select
                                            name="callingOrTexting"
                                            value={data.purpose}
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    purpose:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            required
                                        >
                                            <option value="">
                                                Select purpose
                                            </option>
                                            <option value="texting">
                                                Texting
                                            </option>
                                        </select>
                                    </div>
                                </>
                            )}
                            {data.service_provider === "retell" && (
                                <>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Server Name
                                        </InputLabel>
                                        <TextInput
                                            name="server_name"
                                            value={
                                                data.server_name
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    server_name:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter Server Name."
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Retell API  key
                                        </InputLabel>
                                        <TextInput
                                            name="retell_api"
                                            value={
                                                data.retell_api
                                            }
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    retell_api:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            placeholder="Enter retell API key"
                                            required
                                        ></TextInput>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Retell Agent
                                        </InputLabel>
                                        <select
                                            name="retell_agent_id"
                                            value={data.retell_agent_id}
                                            onChange={(e) => setData({
                                                ...data,
                                                retell_agent_id: e.target.value
                                            })}
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            required
                                        >
                                            <option value="">Select an agent</option>
                                            {agents.map((agent) => (
                                                <option key={agent.agent_id} value={agent.agent_id}>
                                                    {agent.agent_name || `Agent ${agent.agent_id.substring(0, 6)}`}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Calling or Texting
                                        </InputLabel>
                                        <select
                                            name="callingOrTexting"
                                            value={data.purpose}
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    purpose:
                                                        e.target.value,
                                                })
                                            }
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            required
                                        >
                                            <option value="">
                                                Select purpose
                                            </option>

                                            <option value="calling">
                                                Calling
                                            </option>
                                        </select>
                                    </div>
                                </>
                            )}

                            <div>
                                <PrimaryButton
                                    type="submit"
                                    className="w-36 text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Add Server
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
                <div className="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Server Name
                                    </th>
                                    <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Service Provider
                                    </th>

                                    <th className="px-6 py-3 bg-gray-50">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {sendingServers.data.map(
                                    (sendingServer) => (
                                        <tr key={sendingServer.id}>
                                            <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                {
                                                    sendingServer.server_name
                                                }
                                            </td>
                                            <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                {
                                                    sendingServer.service_provider
                                                }
                                            </td>

                                            <td className="px-6 py-1 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    onClick={() =>
                                                        handleUpdateSendingServer(
                                                            sendingServer
                                                        )
                                                    }
                                                    className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-black hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                                >
                                                    <FontAwesomeIcon
                                                        icon={faPen}
                                                        className="fa-xs"
                                                    />
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        handleViewSendingServer(
                                                            sendingServer
                                                        )
                                                    }
                                                    className={`inline-flex items-center px-2 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-black hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                                >
                                                    <FontAwesomeIcon
                                                        icon={faEye}
                                                        className="fa-xs"
                                                    />
                                                </button>

                                            </td>
                                        </tr>
                                    )
                                )}
                            </tbody>
                        </table>
                        <Pagination links={users.meta.links} />
                    </div>
                </div>
            </div>

            <div className="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                <div className="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className="max-w-md mx-auto mt-10">
                        <h1 className="text-2xl font-bold mb-4">
                            Search By Contact
                        </h1>
                        <form
                            onSubmit={handleSearch}
                            className="space-y-4"
                        >
                            <div>
                                <InputLabel className="block text-sm font-medium text-gray-700">
                                    Enter Phone Number
                                </InputLabel>
                                <TextInput
                                    name="organisationName"
                                    value={data.phone_number}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            phone_number:
                                                e.target.value,
                                        })
                                    }
                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter Phone Number"
                                    required
                                ></TextInput>
                            </div>
                            <div>
                                <PrimaryButton
                                    type="submit"
                                    className=" text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Search Contact
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
                <div className="bg-white p-2 rounded-lg shadow-md w-full lg:w-1/2">
                    <div className=" bg-white border-b border-gray-200 overflow-x-auto">
                        {contact && (
                            <div className="mt-6 bg-gray-50 border border-gray-200 rounded-lg ">
                                <h2 className="text-xl font-semibold text-gray-800 mb-4">Contact Details</h2>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full bg-white border border-gray-300">
                                        <thead>
                                            <tr className="bg-gray-200">
                                                <th className="px-4 py-2 border border-gray-300 text-left text-sm font-semibold text-gray-700">Field</th>
                                                <th className="px-4 py-2 border border-gray-300 text-left text-sm font-semibold text-gray-700">Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">ID</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.id}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">UUID</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.uuid}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">Current Step</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.current_step || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">Created At</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{new Date(contact.created_at).toLocaleString()}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">Updated At</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{new Date(contact.updated_at).toLocaleString()}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">Name</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.contact_name || 'N/A'}</td>
                                            </tr>

                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">Email</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.email || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">Phone Number</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.phone || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">Address</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.address || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">City</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.city || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">State</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.state || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">Zip Code</td>
                                                <td className="px-4 py-2 border border-gray-300 text-sm text-gray-700">{contact.zipcode || 'N/A'}</td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}

                    </div>
                </div>
            </div>
            <ViewOrgPopup
                showOrgPopup={showOrgPopup}
                setShowOrgPopup={setShowOrgPopup}
                data={data}
            />
            <ViewSendingServerPopup
                showSendingServerPopup={showSendingServerPopup}
                setShowSendingServerPopup={setShowSendingServerPopup}
                data={data}
            />
            <ViewNumberPoolPopup
                showNumberPoolPopup={showNumberPoolPopup}
                setShowNumberPoolPopup={setShowNumberPoolPopup}
                data={data}
            />
            <UpdateOrgPopup
                isOpen={showUpdateOrgPopup}
                onClose={() => setShowUpdateOrgPopup(false)}
                showUpdateOrgPopup={showUpdateOrgPopup}
                setShowUpdateOrgPopup={setShowUpdateOrgPopup}
                data={orgData}
                setData={setOrgData}
                submitOrganisationUpdate={submitOrganisationUpdate}
                handleChange={handleFormChange}
                organisation={organisation}
            />
            <UpdateSendingServerPopup
                isOpen={showUpdateSendingServerPopup}
                onClose={() => setShowUpdateSendingServerPopup(false)}
                showUpdateSendingServerPopup={showUpdateSendingServerPopup}
                setShowUpdateSendingServerPopup={setShowUpdateSendingServerPopup}
                data={sendingServerData}
                setData={setSendingServerData}
                submitServerUpdate={submitServerUpdate}
                agents={agents}
            />
            <UpdateNumberPoolPopup
                isOpen={showUpdateNumberPoolPopup}
                onClose={() => setShowUpdateNumberPoolPopup(false)}
                showUpdateNumberPoolPopup={showUpdateNumberPoolPopup}
                setShowUpdateNumberPoolPopup={setShowUpdateNumberPoolPopup}
                data={numberPoolData}
                setData={setNumberPoolData}
                submitNumberPoolUpdate={submitNumberPoolUpdate}
            />
            <UpdateNumberPopup
                isOpen={showUpdateNumberPopup}
                onClose={() => setShowUpdateNumberPopup(false)}
                showUpdateNumberPopup={showUpdateNumberPopup}
                setShowUpdateNumberPopup={setShowUpdateNumberPopup}
                sendingServers={sendingServers}
                numberPools={numberPools}
                data={numberData}
                setData={setNumberData}
                submitNumberUpdate={submitNumberUpdate}
            />
        </AuthenticatedLayout>
    );
}
