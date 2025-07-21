import React, { useState, useEffect } from "react";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextAreaInput from "./TextAreaInput";
import TextInput from "./TextInput";
import SelectInput from "./SelectInput";

const EditStepModal = ({
    isOpen,
    onClose,
    stepData,
    updateStep,
    deleteStep,
    placeholders,
    spintaxes
}) => {
    console.log(stepData)
    const [validationMessage, setValidationMessage] = useState("");
    const inputJson = JSON.parse(stepData.days_of_week);
    // console.log(inputson);
    // const inputJson = JSON.parse(inputson)
    const daysOfWeek = {};
    for (const day in inputJson) {
        if (inputJson.hasOwnProperty(day)) {
            daysOfWeek[day] = inputJson[day];
        }
    }
    let delay = "";
    let delayUnit = "";
    if (stepData.delay >= 1440) {
        delay = Math.floor(stepData.delay / 1440);
        delayUnit = "days";
        // return `${days} day${days !== 1 ? 's' : ''}`;
    } else if (stepData.delay >= 60) {
        delay = Math.floor(stepData.delay / 60);
        delayUnit = "hours";

        // return `${hours} hour${hours !== 1 ? 's' : ''}`;
    } else {
        delay = stepData.delay;
        delayUnit = "minutes";
        // return `${delayInMinutes} minute${delayInMinutes !== 1 ? 's' : ''}`;
    }
    let batchDelay = "";
    let batchDelayUnit = "";
    if (stepData.batch_delay >= 1440) {
        batchDelay = Math.floor(stepData.batch_delay / 1440);
        batchDelayUnit = "days";
        // return `${days} day${days !== 1 ? 's' : ''}`;
    } else if (stepData.batch_delay >= 60) {
        batchDelay = Math.floor(stepData.batch_delay / 60);
        batchDelayUnit = "hours";

        // return `${hours} hour${hours !== 1 ? 's' : ''}`;
    } else {
        batchDelay = stepData.batch_delay;
        batchDelayUnit = "minutes";
        // return `${delayInMinutes} minute${delayInMinutes !== 1 ? 's' : ''}`;
    }
    const [editedStep, setEditedStep] = useState({
        id: stepData.id,
        content: stepData.content,
        type: stepData.type,
        delay: delay,
        delayUnit: delayUnit,
        stepName: stepData.name,
        custom_sending: stepData.custom_sending === 1, // Convert to boolean for checkbox
        startTime: stepData.start_time,
        endTime: stepData.end_time,
        batchSize: stepData.batch_size,
        batchDelay: batchDelay,
        offerExpiry: stepData.offer_expiry,
        emailSubject: stepData.email_subject,
        batchDelayUnit: batchDelayUnit,
        generatedMessage:stepData.generated_message,
        daysOfWeek: daysOfWeek, // Parse JSON string to object
    });
    // Update state if stepData changes (optional, depending on how you manage updates)
    useEffect(() => {
        setEditedStep({
            id: stepData.id,
            content: stepData.content,
            type: stepData.type,
            delay: delay,
            delayUnit: delayUnit,
            stepName: stepData.name,
            custom_sending: stepData.custom_sending === 1, // Convert to boolean for checkbox
            startTime: stepData.start_time,
            endTime: stepData.end_time,
            batchSize: stepData.batch_size,
            batchDelay: batchDelay,
            offerExpiry: stepData.offer_expiry,
            emailSubject: stepData.email_subject,
            emailMessage: stepData.email_message,
            batchDelayUnit: batchDelayUnit,
            generatedMessage:stepData.generated_message,
            daysOfWeek: daysOfWeek,
        });
    }, [stepData]);
    console.log(editedStep)
    const validateTimes = () => {
        const { startTime, endTime, custom_sending } = editedStep;
        if (custom_sending === 1) {
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
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        const newValue = type === "checkbox" ? checked : value;
        setEditedStep((prevStep) => ({ ...prevStep, [name]: newValue }));
        // If custom_sending is unchecked, reset optional fields
        if (name === "custom_sending" && !checked) {
            setEditedStep((prevStep) => ({
                ...prevStep,
                startTime: "06:00",
                endTime: "23:00",
                batchSize: "20",
                batchDelay: "20",
                batchDelayUnit: "minutes",
                daysOfWeek: {
                    Sunday: true,
                    Monday: true,
                    Tuesday: true,
                    Wednesday: true,
                    Thursday: true,
                    Friday: true,
                    Saturday: true,
                },
            }));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editedStep.type === "AICall" && (!editedStep.emailSubject || editedStep.emailSubject.trim() === "")) {
            setValidationMessage("Email subject is required for AI Call steps, since the AI might decide to send an email instead.");
            return;
        }
        if (validateTimes()) {
            updateStep(editedStep);
            onClose();
        }
    };

    const handleDelete = (e) => {
        e.preventDefault();
        deleteStep(editedStep.id);
        onClose();
    };

    // Function to toggle day selection
    const toggleDay = (day) => {
        const updatedDaysOfWeek = {
            ...editedStep.daysOfWeek,
            [day]: !editedStep.daysOfWeek[day],
        };
        setEditedStep((prevStep) => ({
            ...prevStep,
            daysOfWeek: updatedDaysOfWeek,
        }));
    };

    // Function to render days in 3x3 grid
    const renderDaysGrid = () => {
        const days = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
        ];
        return (
            <div className="grid grid-cols-3 gap-4">
                {days.map((day) => (
                    <div key={day}>
                        <input
                            type="checkbox"
                            id={day}
                            name={`daysOfWeek.${day}`}
                            checked={editedStep.daysOfWeek[day]}
                            onChange={() => toggleDay(day)}
                            className="mr-2"
                        />
                        <label htmlFor={day}>{day}</label>
                    </div>
                ))}
            </div>
        );
    };

    return (
        <div
            className={`fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50 ${isOpen ? "" : "hidden"
                }`}
        >
            <div className="bg-white p-6 rounded-lg shadow-lg relative max-w-md w-full h-full overflow-auto">
                <button
                    className="absolute top-0 right-0 mt-2 mr-2 text-gray-600 hover:text-gray-800"
                    onClick={onClose}
                >
                    &#x2715;
                </button>
                <div className="mt-4 text-center flex flex-col justify-center space-y-4">
                    <InputLabel forInput="name" value="Step Name" className="text-lg font-semibold text-gray-700" />
                    <TextInput
                        type="text"
                        name="stepName"
                        value={editedStep.stepName}
                        onChange={handleChange}
                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <div className="mb-4">
                        <InputLabel
                            htmlFor="message"
                            className="block text-sm font-medium text-gray-700"
                        >
                            Available Tags
                        </InputLabel>
                        <div className="mt-1 bg-gray-50 p-4 rounded-md shadow-inner">
                            {placeholders.length > 0 && (
                                <div className="flex space-x-4">
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside text-sm text-gray-600">
                                            {placeholders
                                                .slice(0, Math.ceil(placeholders.length / 2))
                                                .map((placeholder, index) => (
                                                    <li key={index}>{placeholder}</li>
                                                ))}
                                        </ul>
                                    </div>
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside text-sm text-gray-600">
                                            {placeholders
                                                .slice(Math.ceil(placeholders.length / 2))
                                                .map((placeholder, index) => (
                                                    <li key={index}>{placeholder}</li>
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
                            className="block text-sm font-medium text-gray-700"
                        >
                            Spintaxes
                        </InputLabel>
                        <div className="mt-1 bg-gray-50 p-4 rounded-md shadow-inner">
                            {spintaxes.length > 0 && (
                                <div className="flex space-x-4">
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside text-sm text-gray-600">
                                            {spintaxes
                                                .slice(0, Math.ceil(spintaxes.length / 2))
                                                .map((spintax, index) => (
                                                    <li key={index}>{spintax.content}</li>
                                                ))}
                                        </ul>
                                    </div>
                                    <div className="flex-1">
                                        <ul className="list-disc list-inside text-sm text-gray-600">
                                            {spintaxes
                                                .slice(Math.ceil(spintaxes.length / 2))
                                                .map((spintax, index) => (
                                                    <li key={index}>{spintax.content}</li>
                                                ))}
                                        </ul>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                    <InputLabel forInput="content" value="Content (This also doubles as the email body if the step involves email sending)" className="text-lg font-semibold text-gray-700" />
                    {editedStep.generatedMessage==1 ? (
                           <div className="text-gray-500 italic bg-gray-50 p-4 rounded-md shadow-inner">{editedStep.content}</div>      
                            ) : (
                        <TextAreaInput
                            type="text"
                            name="content"
                            value={editedStep.content}
                            onChange={handleChange}
                            className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    )}


                    <InputLabel forInput="editType" value="Message Type" className="text-lg font-semibold text-gray-700" />
                    <SelectInput
                        name="type"
                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                        defaultValue={editedStep.type || ""}
                        onChange={handleChange}
                    >
                        <option value={editedStep.type}>
                            {editedStep.type}
                        </option>
                        <option value="SMS">SMS</option>
                        <option value="Voicemail">Voicemail</option>
                        <option value="VoiceCall">Voicecall</option>
                        <option value="VoiceMMS">VoiceMMS</option>
                        <option value="Offer">Offer</option>
                        <option value="Email">Email</option>
                        <option value="AICall">AICall</option>
                    </SelectInput>
                    <InputLabel forInput="offerExpiry" value="Offer Expiry Date (if Offer selected)" className="text-lg font-semibold text-gray-700" />
                    <input
                        type="date"  // Changed from "text" to "date"
                        name="offerExpiry"
                        value={editedStep.offerExpiry}
                        onChange={handleChange}
                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <InputLabel forInput="emailSubject" value="Email Subject (if Email selected)" className="text-lg font-semibold text-gray-700" />
                    <input
                        type="text"  // Changed from "text" to "date"
                        name="emailSubject"
                        value={editedStep.emailSubject}
                        onChange={handleChange}
                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                    />

                    <div className="flex items-center mt-4">
                        <div className="mr-2 w-2/3">
                            <InputLabel
                                forInput="delay"
                                value="No Response Delay"
                                className="text-lg font-semibold text-gray-700"
                            />
                            <input
                                type="number"
                                min="1"
                                name="delay"
                                required
                                value={editedStep.delay}
                                onChange={handleChange}
                                className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                        <div className="w-1/3">
                            <InputLabel forInput="delayUnit" value="Unit" className="text-lg font-semibold text-gray-700" />
                            <select
                                name="delayUnit"
                                value={editedStep.delayUnit}
                                onChange={handleChange}
                                className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="minutes">Minutes</option>
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                                <option value="seconds">Seconds</option>
                            </select>
                        </div>
                    </div>

                    <div className="mt-2">
                        <input
                            type="checkbox"
                            id="customSending"
                            name="custom_sending"
                            checked={editedStep.custom_sending}
                            onChange={handleChange}
                            className="mr-2"
                        />
                        <label htmlFor="customSending" className="text-lg font-semibold text-gray-700">
                            Custom Sending Schedule
                        </label>
                    </div>
                    {editedStep.custom_sending && (
                        <>
                            <InputLabel
                                forInput="startTime"
                                value="Start Time"
                                className="text-lg font-semibold text-gray-700"
                            />
                            <input
                                type="time"
                                name="startTime"
                                value={editedStep.startTime}
                                onChange={handleChange}
                                className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <InputLabel forInput="endTime" value="End Time" className="text-lg font-semibold text-gray-700" />
                            <input
                                type="time"
                                name="endTime"
                                value={editedStep.endTime}
                                onChange={handleChange}
                                className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <InputLabel
                                forInput="batchSize"
                                value="Batch Size"
                                className="text-lg font-semibold text-gray-700"
                            />
                            <input
                                type="number"
                                name="batchSize"
                                value={editedStep.batchSize}
                                onChange={handleChange}
                                className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <div className="flex items-center mt-4">
                                <div className="mr-2 w-2/3">
                                    <InputLabel
                                        forInput="delay"
                                        value="Batch Delay"
                                        className="text-lg font-semibold text-gray-700"
                                    />
                                    <input
                                        type="number"
                                        min="1"
                                        name="batchDelay"
                                        required
                                        value={editedStep.batchDelay}
                                        onChange={handleChange}
                                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                </div>
                                <div className="w-1/3">
                                    <InputLabel
                                        forInput="batchDelayUnit"
                                        value="Unit"
                                        className="text-lg font-semibold text-gray-700"
                                    />
                                    <select
                                        name="batchDelayUnit"
                                        value={editedStep.batchDelayUnit}
                                        onChange={handleChange}
                                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm text-center focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="minutes">Minutes</option>
                                        <option value="hours">Hours</option>
                                        <option value="days">Days</option>
                                        <option value="seconds">Seconds</option>

                                    </select>
                                </div>
                            </div>
                            <div className="mt-2">{renderDaysGrid()}</div>
                        </>
                    )}
                    {validationMessage && (
                        <div className="mt-4 text-red-500">
                            {validationMessage}
                        </div>
                    )}
                    <div className="flex justify-center mt-2">
                        <PrimaryButton
                            onClick={handleSubmit}
                            className="text-center mr-3 max-w-48"
                        >
                            Update Step
                        </PrimaryButton>
                        <PrimaryButton
                            onClick={handleDelete}
                            className="text-center bg-red-600 max-w-48"
                        >
                            Delete Step
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default EditStepModal;
