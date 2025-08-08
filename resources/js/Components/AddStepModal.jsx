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
    spintaxes,
    files
}) => {
    const [validationMessage, setValidationMessage] = useState("");

    // Ensure make_second_call defaults to 0
    React.useEffect(() => {
        setNewStepData(prev => ({
            ...prev,
            make_second_call: typeof prev.make_second_call === 'undefined' ? 0 : prev.make_second_call
        }));
    }, [isOpen]);

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
        if (newStepData.type === "AICall" && (!newStepData.emailSubject || newStepData.emailSubject.trim() === "")) {
            setValidationMessage("Email subject is required for AI Call steps, since the AI might decide to send an email instead.");
            return;
        }
        if (validateTimes()) {
            addStep();
            onClose();
        }
    };
    console.log(spintaxes)
    return (
        <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div className="bg-white p-6 rounded-lg shadow-lg relative max-w-lg w-full h-full overflow-auto">
                <button
                    className="absolute top-4 right-4 text-gray-600 hover:text-gray-800 text-xl"
                    onClick={onClose}
                >
                    &#x2715;
                </button>
                <div className="mt-6 text-center flex flex-col justify-center space-y-6">
                    <div>
                        <InputLabel forInput="name" value="New Step Name" className="text-lg font-semibold" />
                        <input
                            type="text"
                            name="stepName"
                            required
                            value={newStepData.stepName}
                            onChange={handleChange}
                            className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                        />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="message"
                            className="block text-lg font-semibold"
                        >
                            Available Tags
                        </InputLabel>
                        <div className="mt-2 bg-gray-100 p-4 rounded-md shadow-inner">
                            {placeholders.length > 0 && (
                                <div className="flex space-x-4">
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside text-sm text-gray-700">
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
                                        <ul className="list-disc list-inside text-sm text-gray-700">
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
                    <div>
                        <InputLabel
                            htmlFor="message"
                            className="block text-lg font-semibold"
                        >
                            Spintaxes
                        </InputLabel>
                        <div className="mt-2 bg-gray-100 p-4 rounded-md shadow-inner">
                            {spintaxes.length > 0 && (
                                <div className="flex space-x-4">
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside text-sm text-gray-700">
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
                                        <ul className="list-disc list-inside text-sm text-gray-700">
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
                    <div>
                        <InputLabel forInput="content" value="Content (This also doubles as the email body if the step involves email sending)" className="text-lg font-semibold" />
                        <TextAreaInput
                            type="text"
                            name="content"
                            required
                            value={newStepData.content}
                            onChange={handleChange}
                            className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                        />
                    </div>
                    <div className="flex items-center space-x-4">
                        <div className="flex-1">
                            <InputLabel
                                forInput="delay"
                                value="No Response Delay"
                                className="text-lg font-semibold"
                            />
                            <input
                                type="number"
                                min="1"
                                name="delay"
                                required
                                value={newStepData.delay}
                                onChange={handleChange}
                                className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                            />
                        </div>
                        <div>
                            <InputLabel
                                forInput="delayUnit"
                                value="Delay Unit"
                                className="text-lg font-semibold"
                            />
                            <select
                                name="delayUnit"
                                value={newStepData.delayUnit}
                                onChange={handleChange}
                                className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                            >
                                <option value="minutes">Minutes</option>
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                                <option value="seconds">Seconds</option>
                            </select>
                        </div>
                    </div>

                    <InputLabel forInput="type" value="Message Type" className="text-lg font-semibold" />
                    <SelectInput
                        name="type"
                        required
                        className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
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
                        <option value="AICall">AICall</option>

                    </SelectInput>
                    <InputLabel forInput="offerExpiry" value="Enter Expiry Date (required if you choose Offer)" className="text-lg font-semibold" />
                    <input
                        type="date"  // Changed from "text" to "date"
                        name="offerExpiry"
                        value={newStepData.offerExpiry}
                        onChange={handleChange}
                        className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                    />
                    <InputLabel forInput="emailSubject" value="Enter Email Subject (required if you choose Email)" className="text-lg font-semibold" />
                    <input
                        type="text"
                        name="emailSubject"
                        value={newStepData.emailSubject}
                        onChange={handleChange}
                        className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                    />
                    {/* File selection UI below email subject */}
                    {files && files.length > 0 && (
                        <div className="mt-2 text-left">
                            <InputLabel value="Select Template Files (check to include)" className="text-md font-semibold" />
                            <div className="grid grid-cols-2 gap-4">
                                {[0, 1].map(col => (
                                    <ul key={col} className="space-y-1">
                                        {files
                                            .filter((_, idx) => idx % 2 === col)
                                            .map((file) => (
                                                <li key={file.id} className="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        checked={Array.isArray(newStepData.selectedFileIds) && newStepData.selectedFileIds.includes(file.id)}
                                                        onChange={e => {
                                                            setNewStepData(prev => {
                                                                let ids = Array.isArray(prev.selectedFileIds) ? [...prev.selectedFileIds] : [];
                                                                if (e.target.checked) {
                                                                    ids.push(file.id);
                                                                } else {
                                                                    ids = ids.filter(id => id !== file.id);
                                                                }
                                                                return { ...prev, selectedFileIds: ids };
                                                            });
                                                        }}
                                                        className="mr-2"
                                                    />
                                                    <span className="truncate max-w-[180px] block" title={file.name || file.filename || file.original_name || file.path?.split('/').pop() || 'File'}>
                                                        {file.name || file.filename || file.original_name || file.path?.split('/').pop() || 'File'}
                                                    </span>
                                                </li>
                                            ))}
                                    </ul>
                                ))}
                            </div>
                        </div>
                    )}
                
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
                                    className="text-lg font-semibold"
                                />
                                <input
                                    type="time"
                                    name="startTime"
                                    value={newStepData.startTime}
                                    onChange={handleChange}
                                    className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                                />
                                <InputLabel
                                    forInput="endTime"
                                    value="End Time"
                                    className="text-lg font-semibold"
                                />
                                <input
                                    type="time"
                                    name="endTime"
                                    value={newStepData.endTime}
                                    onChange={handleChange}
                                    className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                                />
                            </div>
                            <InputLabel
                                forInput="Batch size"
                                value="Batch Size"
                                className="text-lg font-semibold"
                            />
                            <input
                                type="number"
                                min="1"
                                name="batchSize"
                                value={newStepData.batchSize}
                                onChange={handleChange}
                                className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                            />
                            <div className="flex items-center mt-4 space-x-4">
                                <div className="flex-1">
                                    <InputLabel
                                        forInput="delay"
                                        value="Batch Delay"
                                        className="text-lg font-semibold"
                                    />
                                    <input
                                        type="number"
                                        min="1"
                                        name="batchDelay"
                                        required
                                        value={newStepData.batchDelay}
                                        onChange={handleChange}
                                        className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
                                    />
                                </div>
                                <div className="w-1/3">
                                    <InputLabel
                                        forInput="BatchDelayUnit"
                                        value="Unit"
                                        className="text-lg font-semibold"
                                    />
                                    <select
                                        name="BatchDelayUnit"
                                        value={newStepData.BatchDelayUnit}
                                        onChange={handleChange}
                                        className="mt-2 block w-full border border-gray-300 rounded-md shadow-sm text-center p-2"
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
                    <div>
                        <label className="flex items-center mt-2">
                            <input
                                type="checkbox"
                                name="make_second_call"
                                checked={!!newStepData.make_second_call}
                                onChange={e => setNewStepData(prev => ({ ...prev, make_second_call: e.target.checked ? 1 : 0 }))}
                                className="mr-2"
                            />
                            <span className="text-gray-800">Make Second Call (If doing AI calls)_</span>
                        </label>
                    </div>

                    {validationMessage && (
                        <div className="mt-4 text-red-500 text-sm font-medium">
                            {validationMessage}
                        </div>
                    )}

                    <div className="flex justify-center mt-6">
                        <PrimaryButton
                            onClick={handleAddStep}
                            className="text-center px-6 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700"
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
