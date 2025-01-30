import React, { useState } from "react";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import { Head, Link, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import TextAreaInput from "@/Components/TextAreaInput";
import axios from "axios";
// import { Tooltip } from 'react-tooltip'
import Tooltip from "@/Components/Tooltip";

export default function Create({ success, auth, contactGroups, voices }) {
    const { data, setData, post, errors, processing, reset } = useForm({
        contact_group: "",
        message: "",
        voice: "",
        detection_duration: 3,
        batch_size: 15,
        delay_time: 20,
        agent_phone_number: "",
        country_code: "+1", // Default country code
    });

    const [placeholders, setPlaceholders] = useState([]);
    const [loading, setLoading] = useState(false);

    const handleContactGroupChange = async (e) => {
        const contactGroupId = e.target.value;
        setData("contact_group", contactGroupId);

        if (contactGroupId) {
            setLoading(true);
            setPlaceholders([]);
            try {
                const response = await axios.post(route("placeholders"), {
                    group_id: contactGroupId,
                });
                setPlaceholders(response.data);
            } catch (error) {
                console.error("Error fetching placeholders:", error);
            } finally {
                setLoading(false);
            }
        } else {
            setPlaceholders([]);
        }
    };

    const validatePhoneNumber = (phoneNumber) => {
        // Example validation logic (you can adjust this based on your requirements)
        return /^\+?\d{1,3}[- ]?\d{3,}$/i.test(phoneNumber);
    };

    const onsubmit = (e) => {
        e.preventDefault();

        // Example validation before submitting (adjust as needed)
        if (!validatePhoneNumber(data.agent_phone_number)) {
            alert("Please enter a valid phone number.");
            return;
        }

        let formData = new FormData();
        formData.append("contact_group", data.contact_group);
        formData.append("message", data.message);
        formData.append("voice", data.voice);
        formData.append("detection_duration", data.detection_duration);
        formData.append("batch_size", data.batch_size);
        formData.append("delay_time", data.delay_time);
        formData.append("agent_phone_number", data.agent_phone_number);
        formData.append("country_code", data.country_code);
        post(route("call"), formData);
        reset();
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Make Calls" />
            <div className="container min-h-screen mx-auto">
                <div className="flex min-h-screen md:items-center">
                    <div className="w-full p-2">
                        {success && (
                            <div className="bg-green-500 text-center text-white relative">
                                {success}
                            </div>
                        )}
                        <Link className="hover:underline font-bold p-2 rounded mr-2">
                            &larr; Back
                        </Link>
                        <div className=" text-2xl text-center">
                            Make New Calls
                        </div>
                        <form onSubmit={onsubmit} className="max-w-md mx-auto">
                            <div className="mb-4">
                                <InputLabel
                                    htmlFor="contact-group"
                                    className="block text-sm font-medium"
                                >
                                    Contact Group
                                </InputLabel>
                                <select
                                    id="contact-group"
                                    required
                                    name="contact-group"
                                    value={data.contact_group}
                                    onChange={handleContactGroupChange}
                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                >
                                    <option value="">
                                        Select Contact Group to send to
                                    </option>
                                    {contactGroups.map((group) => (
                                        <option
                                            key={group.uid}
                                            value={group.uid}
                                        >
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
                                    className="block text-sm font-medium"
                                >
                                    Choose Voice
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
                                    message={errors.contact_group}
                                    className="mt-2"
                                />
                            </div>
                            <div className="mb-4">
                                <InputLabel
                                    htmlFor="message"
                                    className="block text-sm font-medium"
                                >
                                    Available Tags
                                </InputLabel>
                                <div className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    <div>
                                        {loading && (
                                            <div className="block text-sm font-medium text-green-800">
                                                Loading Tags... Wait
                                            </div>
                                        )}
                                    </div>
                                    {placeholders.length > 0 && (
                                        <div className="flex">
                                            <div className="flex-1">
                                                <ul className="list-disc list-inside">
                                                    {placeholders
                                                        .slice(
                                                            0,
                                                            Math.ceil(
                                                                placeholders.length /
                                                                    2
                                                            )
                                                        )
                                                        .map(
                                                            (
                                                                placeholder,
                                                                index
                                                            ) => (
                                                                <li key={index}>
                                                                    {
                                                                        placeholder
                                                                    }
                                                                </li>
                                                            )
                                                        )}
                                                </ul>
                                            </div>
                                            <div className="flex-1">
                                                <ul className="list-disc list-inside">
                                                    {placeholders
                                                        .slice(
                                                            Math.ceil(
                                                                placeholders.length /
                                                                    2
                                                            )
                                                        )
                                                        .map(
                                                            (
                                                                placeholder,
                                                                index
                                                            ) => (
                                                                <li key={index}>
                                                                    {
                                                                        placeholder
                                                                    }
                                                                </li>
                                                            )
                                                        )}
                                                </ul>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                            <div className="mb-4">
                                <InputLabel
                                    htmlFor="message"
                                    className="block text-sm font-medium "
                                >
                                    Type Your Voice Message
                                </InputLabel>
                                <TextAreaInput
                                    id="message"
                                    name="message"
                                    required
                                    rows="4"
                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    value={data.message}
                                    onChange={(e) =>
                                        setData({
                                            ...data,
                                            message: e.target.value,
                                        })
                                    }
                                ></TextAreaInput>
                                <InputError
                                    message={errors.message}
                                    className="mt-2"
                                />
                            </div>
                            <div className="mb-4">
                                <InputLabel
                                    htmlFor="detection-duration"
                                    className=" text-sm font-medium flex"
                                >
                                    Machine Detection Duration
                                    <Tooltip text="This is the time we allow the application to detect if the call was picked by a human or machine. Setting the number low will reduce the amount of time it takes for callees to hear the voice recording and consequently less hangups. setting the number high will increase the amount of time it takes for the callee to hear the voice recording but also increases the likelihood of a voicemail been left successfully if the callee does not pick">
                                        <span className="ml-2 text-black cursor-pointer">
                                            &#x1F6C8;
                                        </span>
                                    </Tooltip>
                                </InputLabel>
                                <input
                                    type="range"
                                    id="detection-duration"
                                    name="detection-duration"
                                    min="3"
                                    max="20"
                                    value={data.detection_duration}
                                    onChange={(e) =>
                                        setData(
                                            "detection_duration",
                                            e.target.value
                                        )
                                    }
                                    className="w-full"
                                />
                                <div className="text-sm mt-2">
                                    {data.detection_duration} seconds
                                </div>
                                <InputError
                                    message={errors.detection_duration}
                                    className="mt-2"
                                />
                            </div>
                            <div className="mb-4">
                                <InputLabel
                                    htmlFor="batch-size"
                                    className="text-sm font-medium flex"
                                >
                                    Batch Size
                                    <Tooltip text="This is the number of calls that will be send at once before a delay">
                                        <span className="ml-2 text-black cursor-pointer">
                                            &#x1F6C8;
                                        </span>
                                    </Tooltip>
                                </InputLabel>
                                <input
                                    type="number"
                                    id="batch-size"
                                    name="batch-size"
                                    min="1"
                                    value={data.batch_size}
                                    onChange={(e) =>
                                        setData("batch_size", e.target.value)
                                    }
                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                />
                                <InputError
                                    message={errors.batch_size}
                                    className="mt-2"
                                />
                            </div>
                            <div className="mb-4">
                                <InputLabel
                                    htmlFor="delay-time"
                                    className=" text-sm font-medium flex"
                                >
                                    Delay Time (minutes)
                                    <Tooltip text="This is the time delay before sending another number of calls equal to the batch size. For example by default it will send 15 and wait 20 minutes to send another 15 calls">
                                        <span className="ml-2 text-black cursor-pointer">
                                            &#x1F6C8;
                                        </span>
                                    </Tooltip>
                                </InputLabel>
                                <input
                                    type="number"
                                    id="delay-time"
                                    name="delay-time"
                                    min="0"
                                    value={data.delay_time}
                                    onChange={(e) =>
                                        setData("delay_time", e.target.value)
                                    }
                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                />
                                <InputError
                                    message={errors.delay_time}
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
                                    <select
                                        id="country-code"
                                        name="country-code"
                                        value={data.country_code}
                                        onChange={(e) =>
                                            setData(
                                                "country_code",
                                                e.target.value
                                            )
                                        }
                                        className="ml-2 mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-1/4 shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    >
                                        <option value="+1">+1 (US)</option>
                                        <option value="+254">
                                            +254 (Kenya)
                                        </option>
                                        {/* Add more options as needed */}
                                    </select>
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
                            <div className="mt-4">
                                <PrimaryButton
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    {processing ? "Calling..." : "Send Calls "}
                                </PrimaryButton>
                            </div>
                        </form>
                        <div className=" p-4 flex flex-col items-center justify-center">
                            <h3 className="text-2xl text-center">
                                Voices Preview
                            </h3>
                            {voices.map((voice, index) => (
                                <div
                                    key={index}
                                    className="flex items-center space-x-4 mb-4 p-2 border-b border-gray-300"
                                >
                                    <p className="font-bold">
                                        Name: {voice.name}
                                    </p>
                                    <p>Gender: {voice.gender}</p>
                                    <audio controls className="flex-shrink-0">
                                        <source
                                            src={voice.preview_url}
                                            type="audio/mpeg"
                                        />
                                        Your browser does not support the audio
                                        element.
                                    </audio>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
