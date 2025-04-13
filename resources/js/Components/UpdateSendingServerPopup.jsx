import React, { useState, useEffect } from "react";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SelectInput from "./SelectInput";
import TextAreaInput from "./TextAreaInput";
import TextInput from "./TextInput";
import InputError from "./InputError";
import Tooltip from "./Tooltip";

const UpdateSendingServerPopup = ({
    isOpen,
    onClose,
    submitServerUpdate,
    data,
    setData,
    showUpdateSendingServerPopup,
    agents
}) => {
    useEffect(() => {
        const url = `/get_server/${data.sending_server_id}`;

        // Make the GET request
        axios
            .get(url)
            .then((response) => {
                console.log(response.data.sendingServer);
                // Update the state with the response data
                setData(response.data.sendingServer);
            })
            .catch((error) => {
                // Update the state with the error
                // setError("There was an error making the request!");
                console.error(error);
            });
    }, [showUpdateSendingServerPopup]);
    console.log(data);

    if (!showUpdateSendingServerPopup) return null;
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
                    <div>Edit Server</div>

                    <form
                        onSubmit={submitServerUpdate}
                        className="space-y-4"
                    >
                        {data && (
                            <>
                                {data.service_provider === "twilio" && (
                                    <>
                                        <div>
                                            <InputLabel className="block text-sm font-medium text-gray-700">
                                                Server Name
                                            </InputLabel>
                                            <TextInput
                                                name="server_name"
                                                value={data.server_name}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        server_name: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter Server Name."
                                                required
                                            />
                                        </div>
                                        <div>
                                            <InputLabel className="block text-sm font-medium text-gray-700">
                                                Twilio Account SID
                                            </InputLabel>
                                            <TextInput
                                                name="twilioAccountSid"
                                                value={data.twilio_account_sid}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        twilio_account_sid: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter Twilio Account SID"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <InputLabel className="block text-sm font-medium text-gray-700">
                                                Twilio Auth Token
                                            </InputLabel>
                                            <TextInput
                                                name="twilioAuthToken"
                                                value={data.twilio_auth_token}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        twilio_auth_token: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter Twilio Auth Token"
                                                required
                                            />
                                        </div>
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
                                                        purpose: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                required
                                            >
                                                <option value="">Select purpose</option>
                                                <option value="texting">Texting</option>
                                            </select>
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
                                                value={data.server_name}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        server_name: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter Server Name."
                                                required
                                            />
                                        </div>
                                        <div>
                                            <InputLabel className="block text-sm font-medium text-gray-700">
                                                SignalWire Project ID
                                            </InputLabel>
                                            <TextInput
                                                name="signalwireProjectId"
                                                value={data.signalwire_project_id}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        signalwire_project_id: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter SignalWire Project ID"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <InputLabel className="block text-sm font-medium text-gray-700">
                                                SignalWire API Token
                                            </InputLabel>
                                            <TextInput
                                                name="signalwireApiToken"
                                                value={data.signalwire_api_token}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        signalwire_api_token: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter SignalWire API Token"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <InputLabel className="block text-sm font-medium text-gray-700">
                                                SignalWire Space URL
                                            </InputLabel>
                                            <TextInput
                                                name="signalwireTextingSpaceUrl"
                                                value={data.signalwire_space_url}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        signalwire_space_url: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter SignalWire Space URL"
                                                required
                                            />
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
                                                        purpose: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                required
                                            >
                                                <option value="">Select purpose</option>
                                                <option value="texting">Texting</option>
                                                <option value="calling">Calling</option>
                                            </select>
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
                                                value={data.server_name}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        server_name: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter Server Name."
                                                required
                                            />
                                        </div>
                                        <div>
                                            <InputLabel className="block text-sm font-medium text-gray-700">
                                                Websockets Device ID
                                            </InputLabel>
                                            <TextInput
                                                name="websockets_device_id"
                                                value={data.websockets_device_id}
                                                onChange={(e) =>
                                                    setData({
                                                        ...data,
                                                        websockets_device_id: e.target.value,
                                                    })
                                                }
                                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                placeholder="Enter Websockets Device ID"
                                                required
                                            />
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
                            </>
                        )}
                        <div>
                            <PrimaryButton
                                type="submit"
                                className="w-36 text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Edit Server
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default UpdateSendingServerPopup;
