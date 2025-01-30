import React, { useState } from "react";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SelectInput from "./SelectInput";
import TextAreaInput from "./TextAreaInput";

const AddStepModal = ({
    isOpen,
    onClose,
    addStep,
    newStepData,
    setNewStepData,
    placeholders,
    spintaxes
}) => {
    const [validationMessage, setValidationMessage] = useState("");

    if (!isOpen) return null;

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        if (name === "daysOfWeek") {
            setNewStepData((prevData) => ({
                ...prevData,
                daysOfWeek: {
                    ...prevData.daysOfWeek,
                    [value]: checked,
                },
            }));
        } else {
            setNewStepData((prevData) => ({
                ...prevData,
                [name]: type === "checkbox" ? (checked ? 1 : 0) : value,
            }));
        }
    };

    const handleCustomSendingChange = () => {
        setNewStepData((prevData) => ({
            ...prevData,
            isCustomSending: prevData.isCustomSending === 1 ? 0 : 1,
        }));
    };

    const validateTimes = () => {
        const { startTime, endTime, isCustomSending } = newStepData;
        if (isCustomSending === 1) {
            if (!startTime || !endTime) {
                setValidationMessage("Start time and end time are required.");
                return false;
            }
            if (startTime >= endTime) {
                setValidationMessage(
                    "End time must be greater than start time."
                );
                return false;
            }
        }
        setValidationMessage("");
        return true;
    };

    const handleAddStep = () => {
        if (validateTimes()) {
            addStep();
            onClose();
        }
    };
    console.log(spintaxes)
    return (
        <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div className="bg-white p-6 rounded-lg shadow-lg relative max-w-md w-full h-full overflow-auto">
                <button
                    className="absolute top-0 right-0 mt-2 mr-2 text-gray-600 hover:text-gray-800"
                    onClick={onClose}
                >
                    &#x2715;
                </button>
                <div className="mt-4 text-center flex flex-col justify-center">
                    <InputLabel forInput="name" value="New Step Name" />
                    <input
                        type="text"
                        name="stepName"
                        required
                        value={newStepData.stepName}
                        onChange={handleChange}
                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                    />
                    <div className="mb-4">
                        <InputLabel
                            htmlFor="message"
                            className="block text-sm font-medium"
                        >
                            Available Tags
                        </InputLabel>
                        <div className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            {placeholders.length > 0 && (
                                <div className="flex">
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside">
                                            {placeholders
                                                .slice(
                                                    0,
                                                    Math.ceil(
                                                        placeholders.length / 2
                                                    )
                                                )
                                                .map((placeholder, index) => (
                                                    <li key={index}>
                                                        {placeholder}
                                                    </li>
                                                ))}
                                        </ul>
                                    </div>
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside">
                                            {placeholders
                                                .slice(
                                                    Math.ceil(
                                                        placeholders.length / 2
                                                    )
                                                )
                                                .map((placeholder, index) => (
                                                    <li key={index}>
                                                        {placeholder}
                                                    </li>
                                                ))}
                                        </ul>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                    <div className="mb-4">
                        <InputLabel
                            htmlFor="message"
                            className="block text-sm font-medium"
                        >
                            Spintaxes
                        </InputLabel>
                        <div className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            {spintaxes.length > 0 && (
                                <div className="flex">
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside">
                                            {spintaxes
                                                .slice(
                                                    0,
                                                    Math.ceil(
                                                        spintaxes.length / 2
                                                    )
                                                )
                                                .map((spintax, index) => (
                                                    <li key={index}>
                                                        {spintax.content}
                                                    </li>
                                                ))}
                                        </ul>
                                    </div>
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside">
                                            {spintaxes
                                                .slice(
                                                    Math.ceil(
                                                        spintaxes.length / 2
                                                    )
                                                )
                                                .map((spintax, index) => (
                                                    <li key={index}>
                                                        {spintax.content}
                                                    </li>
                                                ))}
                                        </ul>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                    <InputLabel forInput="content" value="Content" />
                    <TextAreaInput
                        type="text"
                        name="content"
                        required
                        value={newStepData.content}
                        onChange={handleChange}
                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                    />
                    <div className="flex items-center mt-4">
                        <div className="mr-2 w-2/3">
                            <InputLabel
                                forInput="delay"
                                value="No Response Delay"
                            />
                            <input
                                type="number"
                                min='1'
                                name="delay"
                                required
                                value={newStepData.delay}
                                onChange={handleChange}
                                className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                            />
                        </div>
                        <div>
                            <InputLabel
                                forInput="delayUnit"
                                value="Delay Unit"
                            />
                            <select
                                name="delayUnit"
                                value={newStepData.delayUnit}
                                onChange={handleChange}
                                className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                            >
                                <option value="minutes">Minutes</option>
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                                <option value="seconds">Seconds</option>
                            </select>
                        </div>
                    </div>

                    <InputLabel forInput="type" value="Message Type" />
                    <SelectInput
                        name="type"
                        required
                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                        defaultValue={newStepData.type || ""}
                        onChange={handleChange}
                    >
                        <option value="">Select Message Type</option>
                        <option value="SMS">SMS</option>
                        <option value="Voicemail">Voicemail</option>
                        <option value="VoiceCall">Voicecall</option>
                        <option value="VoiceMMS">VoiceMMS</option>
                        <option value="Offer">Offer</option>
                        <option value="Email">Email</option>

                    </SelectInput>
                    <InputLabel forInput="offerExpiry" value="Enter Expiry Date (required if you choose Offer)" />
                    <input
                        type="date"  // Changed from "text" to "date"
                        name="offerExpiry"
                        value={newStepData.offerExpiry}
                        onChange={handleChange}
                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                    />
                    <InputLabel forInput="emailSubject" value="Enter Email Subject (required if you choose Email)" />
                    <input
                        type="text"  // Changed from "text" to "date"
                        name="emailSubject"
                        value={newStepData.emailSubject}
                        onChange={handleChange}
                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                    />
                
                    <div className="mt-4">
                        <input
                            type="checkbox"
                            id="customSending"
                            name="isCustomSending"
                            checked={newStepData.isCustomSending === 1}
                            onChange={handleCustomSendingChange}
                            className="mr-2"
                        />
                        <label
                            htmlFor="customSending"
                            className="text-gray-800"
                        >
                            Custom Sending Schedule
                        </label>
                    </div>

                    {newStepData.isCustomSending === 1 && (
                        <div className="mt-4">
                            <div className="grid grid-cols-3 gap-4">
                                {[
                                    "Sunday",
                                    "Monday",
                                    "Tuesday",
                                    "Wednesday",
                                    "Thursday",
                                    "Friday",
                                    "Saturday",
                                ].map((day) => (
                                    <label key={day} className="text-center">
                                        <input
                                            type="checkbox"
                                            name="daysOfWeek"
                                            value={day}
                                            checked={
                                                newStepData.daysOfWeek?.[day] ||
                                                false
                                            }
                                            onChange={handleChange}
                                            className="mr-1"
                                        />
                                        {day}
                                    </label>
                                ))}
                            </div>
                            <div className="mt-4">
                                <InputLabel
                                    forInput="startTime"
                                    value="Start Time"
                                />
                                <input
                                    type="time"
                                    name="startTime"
                                    value={newStepData.startTime}
                                    onChange={handleChange}
                                    className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                />
                                <InputLabel
                                    forInput="endTime"
                                    value="End Time"
                                />
                                <input
                                    type="time"
                                    name="endTime"
                                    value={newStepData.endTime}
                                    onChange={handleChange}
                                    className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                />
                            </div>
                            <InputLabel
                                forInput="Batch size"
                                value="Batch Size"
                            />
                            <input
                                type="number"
                                min="1"
                                name="batchSize"
                                value={newStepData.batchSize}
                                onChange={handleChange}
                                className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                            />
                            {/* <InputLabel
                                forInput="Batch Delay"
                                value="Batch Delay"
                            />
                            <input
                                type="number"
                                min="1"
                                name="batchDelay"
                                value={newStepData.batchDelay}
                                onChange={handleChange}
                                className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                            /> */}
                            <div className="flex items-center mt-4">
                                <div className="mr-2 w-2/3">
                                    <InputLabel
                                        forInput="delay"
                                        value="Batch Delay"
                                    />
                                    <input
                                        type="number"
                                        min="1"
                                        name="batchDelay"
                                        required
                                        value={newStepData.batchDelay}
                                        onChange={handleChange}
                                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                    />
                                </div>
                                <div className="w-1/3">
                                    <InputLabel
                                        forInput="BatchDelayUnit"
                                        value="Unit"
                                    />
                                    <select
                                        name="BatchDelayUnit"
                                        value={newStepData.BatchDelayUnit}
                                        onChange={handleChange}
                                        className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                    >
                                        <option value="minutes">Minutes</option>
                                        <option value="hours">Hours</option>
                                        <option value="days">Days</option>
                                        <option value="seconds">Seconds</option>

                                    </select>
                                </div>
                            </div>
                        </div>
                    )}

                    {validationMessage && (
                        <div className="mt-4 text-red-500">
                            {validationMessage}
                        </div>
                    )}

                    <div className="flex justify-center mt-4">
                        <PrimaryButton
                            onClick={handleAddStep}
                            className="text-center"
                        >
                            Add Step
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AddStepModal;
