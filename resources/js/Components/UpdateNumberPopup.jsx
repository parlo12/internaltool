import React, { useState, useEffect } from "react";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SelectInput from "./SelectInput";
import TextAreaInput from "./TextAreaInput";
import TextInput from "./TextInput";
import InputError from "./InputError";
import Tooltip from "./Tooltip";

const UpdateNumberPopup = ({
    isOpen,
    onClose,
    submitNumberUpdate,
    data,
    setData,
    showUpdateNumberPopup,
    sendingServers,
    numberPools
}) => {
    useEffect(() => {
        const url = `/get_number/${data.number_id}`;

        // Make the GET request
        axios
            .get(url)
            .then((response) => {
                console.log(response.data.number);
                // Update the state with the response data
                setData(response.data.number);
            })
            .catch((error) => {
                // Update the state with the error
                // setError("There was an error making the request!");
                console.error(error);
            });
    }, [showUpdateNumberPopup]);

    if (!showUpdateNumberPopup) return null;
    return (
        <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div className="bg-white  rounded-lg shadow-lg relative max-w-md w-full overflow-auto">
                <button
                    className="absolute top-0 right-0 mt-2 mr-2 text-gray-600 hover:text-gray-800"
                    onClick={onClose}
                >
                    &#x2715;
                </button>
                <div className="mt-4 text-center flex flex-col justify-center p-2">
                    <div>Edit Number</div>
                    <form
                        onSubmit={submitNumberUpdate}
                        className="space-y-4"
                    >
                        {data && (
                            <>
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
                                        name="provider"
                                        value={data.provider || ""} // Ensure the value is set based on data.phone_number_provider
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                provider:
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
                                        value={data.purpose || "calling"} // Default to "calling" if no value exists
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                purpose: e.target.value,
                                            })
                                        }
                                        className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required
                                    >
                                        <option value="calling">Calling</option>
                                        <option value="texting">Texting</option>
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
                                        Edit Number
                                    </PrimaryButton>
                                </div>
                            </>
                        )}

                    </form>
                </div>
            </div>
        </div>
    );
};

export default UpdateNumberPopup;
