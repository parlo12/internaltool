// CopyWorkflowPopup.js
import React, { useState, useEffect } from "react";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
    faPen,
    faCopy,
    faFolderOpen,
    faEye,
    faTrash,
} from "@fortawesome/free-solid-svg-icons";
import { Link } from "@inertiajs/react";
const ViewSendingServerPopup = ({ showSendingServerPopup, setShowSendingServerPopup, data }) => {
    const [sendingServerData, setSendingServerData] = useState(null);
    console.log(sendingServerData);
    useEffect(() => {
        // Define the URL of the route
        const url = `/get_server/${data.sending_server_id}`;

        // Make the GET request
        axios
            .get(url)
            .then((response) => {
                console.log(response.data);
                // Update the state with the response data
                setSendingServerData(response.data.sendingServer);
                console.log(sendingServerData);
            })
            .catch((error) => {
                // Update the state with the error
                // setError("There was an error making the request!");
                console.error(error);
            });
    }, [showSendingServerPopup]);
    if (!showSendingServerPopup) return null;

    return (
        <div className="fixed inset-0 flex items-center justify-center z-10">
            <div className="bg-white p-4 rounded shadow-lg">
                <div>
                    {sendingServerData && (
                        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 ">
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                                    <div className="flex ">
                                        <div className="pr-4">
                                            Server Name:
                                        </div>
                                        <div>{sendingServerData.server_name}</div>
                                    </div>
                                    <div className="flex ">
                                        <div className="pr-4">
                                            Service Provider:
                                        </div>
                                        <div>{sendingServerData.service_provider}</div>
                                    </div>
            
                                    <div className="flex ">
                                        <div className="pr-4">
                                            purpose:
                                        </div>
                                        <div>{sendingServerData.purpose}</div>
                                    </div>
    
                                    {sendingServerData.signalwire_space_url && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                signalwire_space_url:
                                            </div>
                                            <div>
                                                {
                                                    sendingServerData.signalwire_space_url
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {sendingServerData.signalwire_api_token && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                signalwire_api_token:
                                            </div>
                                            <div>
                                                {
                                                    sendingServerData.signalwire_api_token
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {sendingServerData.signalwire_project_id && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                signalwire_project_id:
                                            </div>
                                            <div>
                                                {
                                                    sendingServerData.signalwire_project_id
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {sendingServerData.twilio_auth_token && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                twilio_auth_token:
                                            </div>
                                            <div>
                                                {
                                                    sendingServerData.twilio_auth_token
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {sendingServerData.twilio_account_sid && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                twilio_account_sid:
                                            </div>
                                            <div>
                                                {
                                                    sendingServerData.twilio_account_sid
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {sendingServerData.websockets_device_id && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                websockets device_id:
                                            </div>
                                            <div>
                                                {
                                                    sendingServerData.websockets_device_id
                                                }
                                            </div>
                                        </div>
                                    )}     
                                    {sendingServerData.auth_token && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                websockets auth token:
                                            </div>
                                            <div>
                                                {
                                                    sendingServerData.websockets_auth_token
                                                }
                                            </div>
                                        </div>
                                    )}     
                                    {sendingServerData.websockets_api_url && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                               websockets api url:
                                            </div>
                                            <div>
                                                {
                                                    sendingServerData.websockets_api_url
                                                }
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
                <div className="mt-4 flex justify-end">
                    <PrimaryButton
                        type="button"
                        onClick={() => setShowSendingServerPopup(false)}
                        className="mr-2"
                    >
                        Cancel
                    </PrimaryButton>
                </div>
            </div>
        </div>
    );
};

export default ViewSendingServerPopup;
