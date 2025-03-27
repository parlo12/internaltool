import React, { useState } from "react";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SelectInput from "./SelectInput";
import TextAreaInput from "./TextAreaInput";
import TextInput from "./TextInput";
import InputError from "./InputError";
import Tooltip from "./Tooltip";

const EditWorkflowModal = ({
    isOpen,
    onClose,
    workflow,
    voices,
    callingNumbers,
    textingNumbers,
    numberPools,
    handleSubmit,
    handleChange,
    data,
    setData,
    errors,
}) => {
    console.log(data);
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
                <div className="mt-4 text-center flex flex-col justify-center">
                    <div>Edit Workflow </div>

                    <form onSubmit={handleSubmit} className="p-4">
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
                                    setData({ ...data, voice: e.target.value })
                                }
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                            >
                                <option value="">
                                    Select a voice (Optional)
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
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="agent-phone-number"
                                className="text-sm font-medium flex"
                            >
                                Agent Phone Number
                                <Tooltip text="This is the phone number the calls will be transferred to. Enter a phone number that you have access to, preferably your cell phone number">
                                    <span className="ml-2 text-black cursor-pointer">
                                        &#x1F6C8;
                                    </span>
                                </Tooltip>
                            </InputLabel>
                            <div className="flex">
                                <input
                                    type="text"
                                    id="agent_number"
                                    name="agent_number"
                                    value={data.agent_number}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            agent_number: e.target.value,
                                        })
                                    }
                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Enter phone number"
                                />
                            </div>
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="calling_number"
                                className="text-sm font-medium flex"
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
                                        setData({
                                            ...data,
                                            calling_number: e.target.value,
                                        })
                                    }
                                    className="ml-2 mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                >
                                    <option value="">
                                        Select a calling number
                                    </option>
                                    {callingNumbers.map((number) => (
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
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="texting_number"
                                className="text-sm font-medium flex"
                            >
                                Texting number
                            </InputLabel>
                            <div className="flex">
                                <select
                                    id="texting_number"
                                    name="texting_number"
                                    value={data.texting_number}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            texting_number: e.target.value,
                                        })
                                    }
                                    className="ml-2 mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                >
                                    <option value="">
                                        Select a texting number
                                    </option>
                                    {textingNumbers.map((number) => (
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
                        </div>
                        <div className="mb-4">
                            <InputLabel
                                htmlFor="texting_number"
                                className="text-sm font-medium flex"
                            >
                                Texting number
                            </InputLabel>
                            <div className="flex">
                                <select
                                    id="number_pool_id"
                                    name="number_pool_id"
                                    value={data.number_pool_id}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            number_pool_id: e.target.value,
                                        })
                                    }
                                    className="ml-2 mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                >
                                    <option value="">
                                        Select a Number Pool
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
                            <div className="mb-4">
                                <InputLabel
                                    htmlFor="generated_message"
                                    className=" text-sm font-medium flex"
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
                                        className="ml-2 mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
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
                                    className="mt-2"
                                />
                            </div>
                        </div>
                        <div className="mt-4">
                            <PrimaryButton
                                type="submit"
                                className="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Save Changes
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default EditWorkflowModal;
