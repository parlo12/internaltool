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
const ViewOrgPopup = ({ showOrgPopup, setShowOrgPopup, data }) => {
    const [orgData, setOrgData] = useState(null);
    console.log(orgData);
    useEffect(() => {
        // Define the URL of the route
        const url = `/get_org/${data.org_id}`;

        // Make the GET request
        axios
            .get(url)
            .then((response) => {
                console.log(response.data);
                // Update the state with the response data
                setOrgData(response.data.organisation);
                console.log(orgData);
            })
            .catch((error) => {
                // Update the state with the error
                // setError("There was an error making the request!");
                console.error(error);
            });
    }, [showOrgPopup]);
    if (!showOrgPopup) return null;

    return (
        <div className="fixed inset-0 flex items-center justify-center z-10">
            <div className="bg-white p-4 rounded shadow-lg">
                <div>
                    {orgData && (
                        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 ">
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className="p-6 bg-white border-b border-gray-200 overflow-x-auto">
                                    <div className="flex ">
                                        <div className="pr-4">
                                            Organisation Name:
                                        </div>
                                        <div>{orgData.organisation_name}</div>
                                    </div>
                                    <div className="flex ">
                                        <div className="pr-4">
                                            OpenAI:
                                        </div>
                                        <div>{orgData.openAI}</div>
                                    </div>
                                    <div className="flex ">
                                        <div className="pr-4">
                                            Sending Email:
                                        </div>
                                        <div>{orgData.sending_email}</div>
                                    </div>
                                    <div className="flex ">
                                        <div className="pr-4">
                                            Email Password:
                                        </div>
                                        <div>{orgData.email_password}</div>
                                    </div>
                                    <div className="flex ">
                                        <div className="pr-4">
                                            Calling Service:
                                        </div>
                                        <div>{orgData.calling_service}</div>
                                    </div>
                                    <div className="flex ">
                                        <div className="pr-4">
                                            Texting Service:
                                        </div>
                                        <div>{orgData.texting_service}</div>
                                    </div>
                                    {orgData.signalwire_texting_space_url && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                signalwire_texting_space_url:
                                            </div>
                                            <div>
                                                {
                                                    orgData.signalwire_texting_space_url
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.signalwire_texting_api_token && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                signalwire_texting_api_token:
                                            </div>
                                            <div>
                                                {
                                                    orgData.signalwire_texting_api_token
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.signalwire_texting_project_id && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                signalwire_texting_project_id:
                                            </div>
                                            <div>
                                                {
                                                    orgData.signalwire_texting_project_id
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.twilio_texting_auth_token && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                twilio_texting_auth_token:
                                            </div>
                                            <div>
                                                {
                                                    orgData.twilio_texting_auth_token
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.twilio_texting_account_sid && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                twilio_texting_account_sid:
                                            </div>
                                            <div>
                                                {
                                                    orgData.twilio_texting_account_sid
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.twilio_calling_account_sid && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                twilio_calling_account_sid:
                                            </div>
                                            <div>
                                                {
                                                    orgData.twilio_calling_account_sid
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.twilio_calling_auth_token && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                twilio_calling_auth_token:
                                            </div>
                                            <div>
                                                {
                                                    orgData.twilio_calling_auth_token
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.signalwire_calling_space_url && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                signalwire_calling_space_url:
                                            </div>
                                            <div>
                                                {
                                                    orgData.signalwire_calling_space_url
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.signalwire_calling_api_token && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                signalwire_calling_api_token:
                                            </div>
                                            <div>
                                                {
                                                    orgData.signalwire_calling_api_token
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.signalwire_calling_project_id && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                signalwire_calling_project_id:
                                            </div>
                                            <div>
                                                {
                                                    orgData.signalwire_calling_project_id
                                                }
                                            </div>
                                        </div>
                                    )}
                                    {orgData.device_id && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                device_id:
                                            </div>
                                            <div>
                                                {
                                                    orgData.device_id
                                                }
                                            </div>
                                        </div>
                                    )}     
                                    {orgData.auth_token && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                auth token:
                                            </div>
                                            <div>
                                                {
                                                    orgData.auth_token
                                                }
                                            </div>
                                        </div>
                                    )}     
                                    {orgData.api_url && (
                                        <div className="flex ">
                                            <div className="pr-4">
                                                api url:
                                            </div>
                                            <div>
                                                {
                                                    orgData.api_url
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
                        onClick={() => setShowOrgPopup(false)}
                        className="mr-2"
                    >
                        Cancel
                    </PrimaryButton>
                </div>
            </div>
        </div>
    );
};

export default ViewOrgPopup;
