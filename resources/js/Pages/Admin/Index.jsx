import { Head, Link, router, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import TextAreaInput from "@/Components/TextAreaInput";
import InputLabel from "@/Components/InputLabel";
import React, { useState, useEffect } from "react";
import TextInput from "@/Components/TextInput";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
    faExchangeAlt,
    faEye,
    faPen,
    faTrash,
} from "@fortawesome/free-solid-svg-icons";
import ViewOrgPopup from "@/Components/ViewOrgPopup";
import UpdateOrgPopup from "@/Components/UpdateOrgPopup";

export default function Index({
    auth,
    success,
    error,
    users,
    spintaxes,
    numbers,
    organisations,
    organisation,
}) {
    const [message, setMessage] = useState(null);
    const [errorMessage, setErrorMessage] = useState(null);
    const [showOrgPopup, setShowOrgPopup] = useState(false);
    const [showUpdateOrgPopup, setShowUpdateOrgPopup] = useState(false);
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

    const handleSearch = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post('/contacts/search', {
                phone_number: data.phone_number,
            });

            if (response.data.status === 'success') {
                console.log(response.data.contact);
                setContact(response.data.contact);
                setErrorMessage(null); // Clear any previous error
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
                // Handle success (e.g., show a success message)
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
        calling_service: "",
        signalwire_texting_space_url: "",
        signalwire_texting_api_token: "",
        signalwire_texting_project_id: "",
        twilio_texting_auth_token: "",
        twilio_texting_account_sid: "",
        texting_service: "",
        twilio_calling_account_sid: "",
        twilio_calling_auth_token: "",
        organisation_name: "",
        signalwire_calling_space_url: "",
        signalwire_calling_api_token: "",
        signalwire_calling_project_id: "",
        org_id: "",
        api_key: "",
        user_id: "",
        openAI: "",
        email_password: "",
        sending_email: "",
        api_url:"",
        auth_token:"",
        device_id:""
    });
    const onSubmit = (e) => {
        e.preventDefault();
        post("/store-spintax");
        reset();
    };
    const submitOrganisation = (e) => {
        e.preventDefault();
        post("/store-organisation");
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
    const deleteSpintax = (deletedSpintaxId) => {
        axios
            .delete(`/delete-spintax/${deletedSpintaxId}`, {})
            .then((response) => {
                console.log(response);
                console.log(`Spintax ${response.content} deleted successfully`);
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
                console.log(response);
                console.log(`Number ${response.content} deleted successfully`);
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
    const handleViewOrg = (org) => {
        setData({
            org_id: org.id,
        });
        setShowOrgPopup(true);
    };
    const handleUpdateOrg = (org) => {
        setData({
            org_id: org.id,
        });
        setShowUpdateOrgPopup(true);
    };
    const submitOrganisationUpdate = async (e) => {
        e.preventDefault();
        try {

            const response = await axios.post('/update-organisation', orgData);
            setMessage(`Org update  successfull`);
            setShowUpdateOrgPopup(false)
            setTimeout(() => { window.location.reload() }, 2000);
            console.log('Response:', response.data);
        } catch (error) {
            setErrorMessage("Error switching to org");
            setShowUpdateOrgPopup(false)
            console.error('Error updating org', error);
        }
    };
    const switchOrg = (orgId) => {
        axios
            .get(`/switch-organisation/${orgId}`)
            .then((response) => {
                setMessage(`Switch to org ${orgId} was successfull`);
                console.log(
                    "Organisation switched successfully:",
                    response.data
                );
                window.location.reload();
                // Handle success, e.g., refresh the page or update UI
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
            console.log(apiKey);
            axios
                .post("/submit-api-key", {
                    api_key: apiKey,
                    user_id: userId,
                })
                .then((response) => {
                    console.log(
                        "API Key submitted successfully:",
                        response.data
                    );

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

    // const submitApiKey = () => {
    //     console.log(data);
    //     axios
    //         .post("/submit-api-key", {
    //             api_key: data.api_key,
    //             user_id: data.user_id,
    //         })
    //         .then((response) => {
    //             console.log("API Key submitted successfully:", response.data);
    //         })
    //         .catch((error) => {
    //             console.error("Error submitting API Key:", error);
    //         });
    // };
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
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="text-2xl text-center">
                        Your are now managing org:{" "}
                        {organisation && organisation.organisation_name}
                    </div>
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                            {error && (
                                <div className="bg-red-500 text-white p-2 rounded mb-4">
                                    {error}
                                </div>
                            )}
                            {success && (
                                <div className="bg-green-500 text-white p-2 rounded mb-4">
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
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Admin Status
                                        </th>
                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Organisation
                                        </th>
                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Enter godspeedoffers api & press enter
                                        </th>
                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                                                {user.is_admin
                                                    ? "Admin"
                                                    : "User"}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <select
                                                    value={user.organisation_id}
                                                    onChange={(e) =>
                                                        handleUpdateOrganisation(
                                                            user.id,
                                                            e.target.value
                                                        )
                                                    }
                                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                >
                                                    <option value="">
                                                        {organisations.data.find(
                                                            (org) =>
                                                                org.id ===
                                                                user.organisation_id
                                                        )?.organisation_name ||
                                                            "Select Organisation"}
                                                    </option>
                                                    {organisations.data.map(
                                                        (org) => (
                                                            <option
                                                                key={org.id}
                                                                value={org.id}
                                                            >
                                                                {
                                                                    org.organisation_name
                                                                }
                                                            </option>
                                                        )
                                                    )}
                                                </select>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div className='w-48 overflow-auto'>{user.godspeedoffers_api}</div>
                                                <input
                                                    type="text"
                                                    value={
                                                        apiKeys[user.id] || ''
                                                    }
                                                    onChange={(e) =>
                                                        handleApiKeyChange(
                                                            user.id,
                                                            e.target.value
                                                        )
                                                    }
                                                    onKeyPress={(e) => {
                                                        if (e.key === "Enter") {
                                                            submitApiKey(
                                                                user.id
                                                            );
                                                        }
                                                    }}
                                                    placeholder="Enter API Key"
                                                    className="border  rounded"
                                                />
                                            </td>
                                            <td className=" whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    onClick={() =>
                                                        handleToggleAdmin(
                                                            user.id
                                                        )
                                                    }
                                                    className={`inline-flex items-center px-2 mr-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500`}
                                                >
                                                    {user.is_admin
                                                        ? "Dismiss"
                                                        : "Admit"}
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        deleteUser(
                                                            user
                                                        )
                                                    }
                                                    className={`inline-flex items-center px-2  border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500`}
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
                    <div class="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                        <div class="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
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
                        <div class="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
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
                    <div class="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                        <div class="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
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
                        <div class="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                            <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Number
                                            </th>
                                            <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Purpose
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
                                                    {number.purpose}
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
                    <div class="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                        <div class="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
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
                                    {/* Calling Service */}
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Calling Service
                                        </InputLabel>
                                        <div className="space-x-4">
                                            {/* <label className="inline-flex items-center">
                                                <input
                                                    type="radio"
                                                    name="callingService"
                                                    value="twilio"
                                                    checked={
                                                        data.calling_service ===
                                                        "twilio"
                                                    }
                                                    onChange={(e) => {
                                                        setData({
                                                            ...data,
                                                            calling_service:
                                                                e.target.value,
                                                            // Clear SignalWire calling fields
                                                            signalwire_calling_project_id:
                                                                "",
                                                            signalwire_calling_api_token:
                                                                "",
                                                            signalwire_calling_space_url:
                                                                "",
                                                        });
                                                    }}
                                                    className="form-radio"
                                                    required
                                                />
                                                <span className="ml-2">
                                                    Twilio
                                                </span>
                                            </label> */}
                                            <label className="inline-flex items-center">
                                                <input
                                                    type="radio"
                                                    name="callingService"
                                                    value="signalwire"
                                                    checked={
                                                        data.calling_service ===
                                                        "signalwire"
                                                    }
                                                    onChange={(e) => {
                                                        setData({
                                                            ...data,
                                                            calling_service:
                                                                e.target.value,
                                                            // Clear Twilio calling fields
                                                            twilio_calling_account_sid:
                                                                "",
                                                            twilio_calling_auth_token:
                                                                "",
                                                        });
                                                    }}
                                                    className="form-radio"
                                                    required
                                                />
                                                <span className="ml-2">
                                                    SignalWire
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    {data.calling_service === "twilio" && (
                                        <>
                                            <div>
                                                <InputLabel className="block text-sm font-medium text-gray-700">
                                                    Twilio Account SID
                                                </InputLabel>
                                                <TextInput
                                                    name="twilioCallingAccountSid"
                                                    value={
                                                        data.twilio_calling_account_sid
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            twilio_calling_account_sid:
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
                                                    name="twilioCallingAuthToken"
                                                    value={
                                                        data.twilio_calling_auth_token
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            twilio_calling_auth_token:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="Enter Twilio Auth Token"
                                                    required
                                                ></TextInput>
                                            </div>
                                        </>
                                    )}

                                    {data.calling_service === "signalwire" && (
                                        <>
                                            <div>
                                                <InputLabel className="block text-sm font-medium text-gray-700">
                                                    SignalWire Project ID
                                                </InputLabel>
                                                <TextInput
                                                    name="signalwireCallingProjectId"
                                                    value={
                                                        data.signalwire_calling_project_id
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            signalwire_calling_project_id:
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
                                                    name="signalwireCallingApiToken"
                                                    value={
                                                        data.signalwire_calling_api_token
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            signalwire_calling_api_token:
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
                                                    name="signalwireCallingSpaceUrl"
                                                    value={
                                                        data.signalwire_calling_space_url
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            signalwire_calling_space_url:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="Enter SignalWire Space URL"
                                                    required
                                                ></TextInput>
                                            </div>
                                        </>
                                    )}

                                    {/* Texting Service */}
                                    <div>
                                        <InputLabel className="block text-sm font-medium text-gray-700">
                                            Texting Service
                                        </InputLabel>
                                        <div className="space-x-4">
                                            <label className="inline-flex items-center">
                                                <input
                                                    type="radio"
                                                    name="textingService"
                                                    value="twilio"
                                                    checked={
                                                        data.texting_service ===
                                                        "twilio"
                                                    }
                                                    onChange={(e) => {
                                                        setData({
                                                            ...data,
                                                            texting_service:
                                                                e.target.value,
                                                            // Clear SignalWire texting fields
                                                            signalwire_texting_project_id:
                                                                "",
                                                            signalwire_texting_api_token:
                                                                "",
                                                            signalwire_texting_space_url:
                                                                "",
                                                            api_url:"",
                                                            auth_token:"",
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
                                                    name="textingService"
                                                    value="signalwire"
                                                    checked={
                                                        data.texting_service ===
                                                        "signalwire"
                                                    }
                                                    onChange={(e) => {
                                                        setData({
                                                            ...data,
                                                            texting_service:
                                                                e.target.value,
                                                            // Clear Twilio texting fields
                                                            twilio_texting_account_sid:
                                                                "",
                                                            twilio_texting_auth_token:
                                                                "",
                                                            api_url:"",
                                                            auth_token:"",

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
                                                    name="textingService"
                                                    value="websockets-api"
                                                    checked={
                                                        data.texting_service ===
                                                        "websockets-api"
                                                    }
                                                    onChange={(e) => {
                                                        setData({
                                                            ...data,
                                                            texting_service:
                                                                e.target.value,
                                                            // Clear Twilio texting fields
                                                            twilio_texting_account_sid:
                                                                "",
                                                            twilio_texting_auth_token:
                                                                "",
                                                                signalwire_texting_project_id:
                                                                "",
                                                            signalwire_texting_api_token:
                                                                "",
                                                            signalwire_texting_space_url:
                                                                "",
                                                        });
                                                    }}
                                                    className="form-radio"
                                                    required
                                                />
                                                <span className="ml-2">
                                                    Websockets API
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    {data.texting_service === "twilio" && (
                                        <>
                                            <div>
                                                <InputLabel className="block text-sm font-medium text-gray-700">
                                                    Twilio Account SID
                                                </InputLabel>
                                                <TextInput
                                                    name="twilioTextingAccountSid"
                                                    value={
                                                        data.twilio_texting_account_sid
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            twilio_texting_account_sid:
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
                                                    name="twilioTextingAuthToken"
                                                    value={
                                                        data.twilio_texting_auth_token
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            twilio_texting_auth_token:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="Enter Twilio Auth Token"
                                                    required
                                                ></TextInput>
                                            </div>
                                        </>
                                    )}

                                    {data.texting_service === "signalwire" && (
                                        <>
                                            <div>
                                                <InputLabel className="block text-sm font-medium text-gray-700">
                                                    SignalWire Project ID
                                                </InputLabel>
                                                <TextInput
                                                    name="signalwireTextingProjectId"
                                                    value={
                                                        data.signalwire_texting_project_id
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            signalwire_texting_project_id:
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
                                                    name="signalwireTextingApiToken"
                                                    value={
                                                        data.signalwire_texting_api_token
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            signalwire_texting_api_token:
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
                                                        data.signalwire_texting_space_url
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            signalwire_texting_space_url:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="Enter SignalWire Space URL"
                                                    required
                                                ></TextInput>
                                            </div>
                                        </>
                                    )}
                                     {data.texting_service === "websockets-api" && (
                                        <>
                                            <div>
                                                <InputLabel className="block text-sm font-medium text-gray-700">
                                                    Device ID
                                                </InputLabel>
                                                <TextInput
                                                    name="device_id"
                                                    value={
                                                        data.device_id
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            device_id:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="Enter Device ID"
                                                    required
                                                ></TextInput>
                                            </div>
                                            <div>
                                                <InputLabel className="block text-sm font-medium text-gray-700">
                                                    Auth Token
                                                </InputLabel>
                                                <TextInput
                                                    name="auth_token"
                                                    value={
                                                        data.auth_token
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            auth_token:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="Auth Token"
                                                    required
                                                ></TextInput>
                                            </div>
                                            <div>
                                                <InputLabel className="block text-sm font-medium text-gray-700">
                                                    Api Url
                                                </InputLabel>
                                                <TextInput
                                                    name="api_url"
                                                    value={
                                                        data.api_url
                                                    }
                                                    onChange={(e) =>
                                                        setData({
                                                            ...data,
                                                            api_url:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="API URL"
                                                    required
                                                ></TextInput>
                                            </div>
                                        </>
                                    )}

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
                        <div class="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/2">
                            <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                organisation_name
                                            </th>
                                            <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Calling Service
                                            </th>
                                            <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Texting Service
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
                                                    <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                        {
                                                            organisation.calling_service
                                                        }
                                                    </td>
                                                    <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                        {
                                                            organisation.texting_service
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
                    <div class="flex flex-col lg:flex-row space-y-4 mt-5 lg:space-y-0 lg:space-x-4">
                        <div class="bg-white p-4 rounded-lg shadow-md w-full lg:w-1/2">
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
                        <div class="bg-white p-2 rounded-lg shadow-md w-full lg:w-1/2">
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
                </div>
            </div>
            <ViewOrgPopup
                showOrgPopup={showOrgPopup}
                setShowOrgPopup={setShowOrgPopup}
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
        </AuthenticatedLayout>
    );
}
