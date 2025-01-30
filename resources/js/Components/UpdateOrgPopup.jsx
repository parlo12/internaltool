import React, { useState } from "react";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SelectInput from "./SelectInput";
import TextAreaInput from "./TextAreaInput";
import TextInput from "./TextInput";
import InputError from "./InputError";
import Tooltip from "./Tooltip";

const UpdateOrgPopup = ({
    isOpen,
    onClose,
    submitOrganisationUpdate,
    data,
    setData,
    handleChange,
    organisation,
    errors,
}) => {
    if (!isOpen) return null;
    return (
        <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div className="bg-white  rounded-lg shadow-lg relative max-w-md w-full h-full overflow-auto">
                <button
                    className="absolute top-0 right-0 mt-2 mr-2 text-gray-600 hover:text-gray-800"
                    onClick={onClose}
                >
                    &#x2715;
                </button>
                <div className="mt-4 text-center flex flex-col justify-center p-2">
                    <div>Edit Organisation</div>

                    <form
                        onSubmit={submitOrganisationUpdate}
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
                                        organisation_name: e.target.value,
                                    })
                                }
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Enter organisation name"
                                required
                            ></TextInput>
                        </div>
                        <div>
                            <InputLabel className="block text-sm font-medium text-gray-700">
                                Open AI key
                            </InputLabel>
                            <TextInput
                                name="openAI"
                                value={data.openAI}
                                onChange={(e) =>
                                    setData({
                                        ...data,
                                        openAI: e.target.value,
                                    })
                                }
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Enter openAI Key"
                                required
                            ></TextInput>
                            <InputLabel className="block text-sm font-medium text-gray-700">
                                Sending Email
                            </InputLabel>
                            <TextInput
                                name="sending_email"
                                value={data.sending_email}
                                onChange={(e) =>
                                    setData({
                                        ...data,
                                        sending_email: e.target.value,
                                    })
                                }
                                className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Enter sending email"
                                required
                            ></TextInput>
                            <InputLabel className="block text-sm font-medium text-gray-700">
                                Email Password
                            </InputLabel>
                            <TextInput
                                name="email_password"
                                value={data.email_password}
                                onChange={(e) =>
                                    setData({
                                        ...data,
                                        email_password: e.target.value,
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
                                                calling_service: e.target.value,
                                                // Clear Twilio calling fields
                                                twilio_calling_account_sid: "",
                                                twilio_calling_auth_token: "",
                                            });
                                        }}
                                        className="form-radio"
                                        required
                                    />
                                    <span className="ml-2">SignalWire</span>
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
                                        value={data.twilio_calling_account_sid}
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
                                        value={data.twilio_calling_auth_token}
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
                                            data.texting_service === "twilio"
                                        }
                                        onChange={(e) => {
                                            setData({
                                                ...data,
                                                texting_service: e.target.value,
                                                // Clear SignalWire texting fields
                                                signalwire_texting_project_id:
                                                    "",
                                                signalwire_texting_api_token:
                                                    "",
                                                signalwire_texting_space_url:
                                                    "",
                                                    device_id:'', 
                                                auth_token:"", 
                                                api_url:"",
                                                twilio_texting_account_sid:
                                                    organisation.twilio_texting_account_sid,
                                                twilio_texting_auth_token:
                                                    organisation.twilio_texting_auth_token,
                                            });
                                        }}
                                        className="form-radio"
                                        required
                                    />
                                    <span className="ml-2">Twilio</span>
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
                                                texting_service: e.target.value,
                                                // Clear Twilio texting fields
                                                twilio_texting_account_sid: "",
                                                twilio_texting_auth_token: "",
                                                device_id:'', 
                                                auth_token:"", 
                                                api_url:"",
                                                signalwire_texting_project_id:
                                                    organisation.signalwire_texting_project_id,
                                                signalwire_texting_api_token:
                                                    organisation.signalwire_texting_api_token,
                                                signalwire_texting_space_url:
                                                    organisation.signalwire_texting_space_url,
                                            });
                                        }}
                                        className="form-radio"
                                        required
                                    />
                                    <span className="ml-2">SignalWire</span>
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
                                                texting_service: e.target.value,
                                                // Clear Twilio texting fields
                                                twilio_texting_account_sid: "",
                                                twilio_texting_auth_token: "",
                                                signalwire_texting_project_id:"",
                                                signalwire_texting_api_token:"",
                                                signalwire_texting_space_url:"",
                                                device_id:organisation.device_id, 
                                                auth_token:organisation.auth_token, 
                                                api_url:organisation.api_url,
                                            });
                                        }}
                                        className="form-radio"
                                        required
                                    />
                                    <span className="ml-2">Websockets-api</span>
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
                                        value={data.twilio_texting_account_sid}
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
                                        value={data.twilio_texting_auth_token}
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
                                Edit Organisation
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default UpdateOrgPopup;
