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
            <div className="bg-white  rounded-lg shadow-lg relative max-w-md w-full overflow-auto">
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
