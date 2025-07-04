import React, { useState, useEffect } from "react";
import { Head, Link, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import PrimaryButton from "@/Components/PrimaryButton";
import AddStepModal from "@/Components/AddStepModal";
import EditStepModal from "@/Components/EditStepModal";
import axios from "axios";
import EditWorkflowModal from "@/Components/EditWorkflowModal";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faPen } from "@fortawesome/free-solid-svg-icons";
import { usePage } from "@inertiajs/react";
export default function Create({
    auth,
    workflow,
    steps,
    placeholders,
    spintaxes,
    voices,
    calling_numbers,
    texting_numbers,
    numberPools,
    refererr
}) {
    console.log(refererr);
    const { back } = usePage().props;
    const [stepsState, setStepsState] = useState(steps);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [newStepData, setNewStepData] = useState({
        stepName: "",
        content: "",
        delay: "",
        delayUnit: "minutes",
        type: "",
        offerExpiry: "",
        emailSubject: "",
        emailMessage: "",
        startTime: "08:00",
        endTime: "20:00",
        batchSize: "20",
        batchDelay: "20",
        batchDelayUnit: "minutes",
        isCustomSending: 0,
        daysOfWeek: {
            Sunday: true,
            Monday: true,
            Tuesday: true,
            Wednesday: true,
            Thursday: true,
            Friday: true,
            Saturday: true,
        },
        workflow: workflow.id,
    });

    const [editModalOpen, setEditModalOpen] = useState(false);
    const [selectedStep, setSelectedStep] = useState(null);
    const [success, setSuccess] = useState(null);
    const [error, setError] = useState(null);
    const [editWorkflowModalOpen, setEditWorkflowModalOpen] = useState(false);
    const [workflowData, setWorkflowData] = useState({
        name: workflow.name,
        contact_group: workflow.contact_group,
        voice: workflow.voice,
        agent_number: workflow.agent_number,
        country_code: workflow.country_code,
        calling_number: workflow.calling_number,
        texting_number: workflow.texting_number,
    });
    const [errors, setErrors] = useState({});

    // Form change handler
    const handleFormChange = (e) => {
        setWorkflowData({ ...workflowData, [e.target.name]: e.target.value });
    };

    // Form submission handler
    const handleFormSubmit = (e) => {
        e.preventDefault();

        axios
            .put(`/workflows/${workflow.id}`, workflowData)
            .then((response) => {
                setEditWorkflowModalOpen(false);
                setSuccess("Workflow Edited successfully!");
                window.location.reload();
            })
            .catch((error) => {
                console.log(error.response.data.errors);
                setError("Error Editing Workflow:");

            });
    };
    const formatDelay = (delayInMinutes) => {
        if (delayInMinutes >= 1440) {
            const days = Math.floor(delayInMinutes / 1440);
            return `${days} day${days !== 1 ? "s" : ""}`;
        } else if (delayInMinutes >= 60) {
            const hours = Math.floor(delayInMinutes / 60);
            return `${hours} hour${hours !== 1 ? "s" : ""}`;
        } else {
            //const formattedDelay = ;
            return `${parseFloat(delayInMinutes).toFixed(2)} minute${delayInMinutes !== 1 ? "s" : ""
                }`;
        }
    };
    const deleteWorkflow = (event) => {
        event.preventDefault();

        // Ask the user for confirmation
        const confirmDelete = window.confirm(`Are you sure you want to delete the workflow: ${workflow.name}? This cannot be undone`);

        if (confirmDelete) {
            // Proceed with the deletion
            window.location.href = route('delete-workflow', workflow.id);
        }
    };
    const addStep = () => {
        // Check if the required fields are filled
        if (newStepData.type === "Offer" && newStepData.offerExpiry.trim() === "") {
            setError("The offer expiry field is required if you are sending an offer.");
            console.error("The offer expiry field is required if you are sending an offer.");
            return;
        }
        if (newStepData.type === "Email" && newStepData.emailSubject.trim() === "") {
            setError("The email subject field is required if you are sending an email.");
            console.error("The  email subject field is required if you are sending an email.");
            return;
        }
        // Check if the step name and content are filled
        if (newStepData.stepName.trim() !== "" && newStepData.content.trim() !== "") {
            const newStep = {
                ...newStepData,
                id: stepsState.length + 1,
            };


            axios
                .post("/store-step", newStep)
                .then((response) => {
                    setStepsState(response.data.steps);
                    setIsModalOpen(false);
                    setSuccess("Step added successfully!");

                    // Reset form data
                    setNewStepData({
                        stepName: "",
                        content: "",
                        delay: "",
                        delayUnit: "minutes",
                        type: "",
                        startTime: "",
                        endTime: "",
                        batchSize: "",
                        batchDelay: "",
                        offerExpiry: "",
                        emailSubject: "",
                        emailMessage: "", // Reset the offer expiry
                        batchDelayUnit: "minutes",
                        isCustomSending: 0,
                        daysOfWeek: {
                            Sunday: true,
                            Monday: true,
                            Tuesday: true,
                            Wednesday: true,
                            Thursday: true,
                            Friday: true,
                            Saturday: true,
                        },
                        workflow: workflow.id,
                    });
                })
                .catch((error) => {
                    console.error("Error adding step:", error.response?.data || error.message);
                });
        } else {
            // If step name or content are missing
            setError("Please fill in all required fields.");
            console.error("Please fill in all required fields.");
        }
    };


    const openEditModal = (step) => {

        setSelectedStep(step);
        setEditModalOpen(true);
    };

    const updateStep = (updatedStep) => {
        updatedStep.workflow = workflow.id;
        const updatedSteps = stepsState.map((step) =>
            step.id === updatedStep.id ? updatedStep : step
        );

        axios
            .post("/update-step", updatedStep)
            .then((response) => {
                setStepsState(response.data.steps);
                setEditModalOpen(false);
                setSuccess("Step updated successfully!");
            })
            .catch((error) => {
                setError("Error updating step:");
                console.error("Error updating step:", error);
            });
    };

    const startWorkflow = () => {
        axios
            .get(`/start-workflow/${workflow.id}`)
            .then((response) => {
                setSuccess("Workflow started  successfully!");
                window.location.reload();
            })
            .catch((error) => {
                setError("Error Starting step");
                console.error("Error Starting Workflow:", error);
            });
    };
    useEffect(() => {
        stepsState.forEach((step) => {
            // Fetch the response count for each step
            getStepResponses(step.id);
        });
    }, [stepsState]);

    function getStepResponses(stepId) {
        fetch(`/step-responses/${stepId}`)
            .then((response) => response.json())
            .then((data) => {
                // Update the respective step's response count
                const responseElement = document.getElementById(`response-count-${stepId}`);
                if (responseElement) {
                    responseElement.innerText = data.count;
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                const responseElement = document.getElementById(`response-count-${stepId}`);
                if (responseElement) {
                    responseElement.innerText = 'Error';
                }
            });
    }
    const pauseWorkflow = () => {
        axios
            .get(`/pause-workflow/${workflow.id}`)
            .then((response) => {
                setSuccess("Workflow Paused  successfully!");
                window.location.reload();
            })
            .catch((error) => {
                setError("Error pausing step");
                console.error("Error Pausing Workflow:", error);
            });
    };

    const deleteStep = (deletedStepId) => {
        axios
            .delete(`/delete-step/${deletedStepId}`, {
                data: { workflow: workflow.id },
            })
            .then((response) => {
                setStepsState(response.data.steps);
                setSuccess("Step deleted successfully!");
            })
            .catch((error) => {
                setError("Error updating step");
                console.error(
                    "Error updating step",
                    error.response?.data || error.message
                );
            });
    };

    useEffect(() => {
        if (success) {
            const timer = setTimeout(() => {
                setSuccess(null);
            }, 3000);
            return () => clearTimeout(timer);
        }
    }, [success]);

    useEffect(() => {
        if (error) {
            const timer = setTimeout(() => {
                setError(null);
            }, 3000);
            return () => clearTimeout(timer);
        }
    }, [error]);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create workflow" />
            <div className="container mx-auto px-4 py-8 min-h-screen">
                <div className="flex flex-col items-center">
                    <div className="mt-8 text-2xl font-semibold text-gray-800">
                        Workflow: {workflow.name}
                        <button
                            onClick={() => setEditWorkflowModalOpen(true)}
                            className="ml-2 text-blue-500 hover:text-blue-700"
                        >
                            <FontAwesomeIcon icon={faPen} className="fa-xs" />
                        </button>
                    </div>
                    {success && (
                        <div className="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded shadow-lg">
                            {success}
                        </div>
                    )}
                    {error && (
                        <div className="fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white px-6 py-3 rounded shadow-lg">
                            {error}
                        </div>
                    )}
                    <div className="w-full max-w-4xl p-6 rounded-lg shadow-lg bg-white">
                        <div>
                            


                            {stepsState.map((step, index) => (
                                <div key={step.id} className="mb-6">
                                    <div
                                        className="border border-gray-300 p-4 rounded-lg cursor-pointer hover:shadow-md"
                                        onClick={() => openEditModal(step)}
                                    >
                                        <div className="flex justify-between items-center">
                                            <div className="font-medium text-gray-700">
                                                {index + 1}. {step.name}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                Responses: <span id={`response-count-${step.id}`}>Loading...</span>
                                            </div>
                                        </div>
                                        <div className="mt-2 text-gray-600">
                                            {step.content}
                                        </div>
                                        <div className="flex justify-end mt-4 text-sm text-gray-500">
                                            <span>Message Type: {step.type}</span>
                                        </div>
                                    </div>
                                    <div className="text-center text-gray-500 mt-4">
                                        <span className="text-lg">&darr;</span>
                                    </div>
                                    <div className="flex justify-center my-5">
                                        <div className="p-4 border border-gray-300 rounded-lg bg-gray-50">
                                            <span className="text-sm text-gray-700">
                                                Delay: {formatDelay(step.delay)}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="text-center text-gray-500">
                                        If no response
                                    </div>
                                    <div className="text-center text-gray-500 mt-2">
                                        <span className="text-lg">&darr;</span>
                                    </div>
                                </div>
                            ))}
                            {stepsState.length > 0 && (
                                <div className="flex justify-center mt-6">
                                    <div className="border border-gray-300 p-4 text-center w-24 rounded-lg bg-gray-50">
                                        End
                                    </div>
                                </div>
                            )}
                        </div>
                        <div className="flex flex-col items-center mt-6 space-y-4">
                            <PrimaryButton
                                onClick={() => setIsModalOpen(true)}
                                className="w-full max-w-xs py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow"
                            >
                                Add Step
                            </PrimaryButton>
                            {stepsState.length > 0 && (
                                <div className="w-full flex flex-col sm:flex-row justify-center gap-4">
                                    <PrimaryButton
                                        onClick={workflow.active ? pauseWorkflow : startWorkflow}
                                        className={`w-full max-w-xs py-2 text-white rounded-lg shadow ${workflow.active
                                            ? "bg-yellow-600 hover:bg-yellow-700"
                                            : "bg-green-600 hover:bg-green-700"
                                            }`}
                                    >
                                        {workflow.active ? "Pause Workflow" : "Start Workflow"}
                                    </PrimaryButton>
                                    <Link
                                        href={route("contacts.index", workflow.id)}
                                        className="w-full max-w-xs py-2 text-center text-white bg-green-600 hover:bg-green-700 rounded-lg shadow"
                                    >
                                        Workflow Progress
                                    </Link>
                                    <button
                                        onClick={deleteWorkflow}
                                        className="w-full max-w-xs py-2 text-center text-white bg-red-600 hover:bg-red-700 rounded-lg shadow"
                                    >
                                        Delete This Workflow
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
            <AddStepModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                addStep={addStep}
                placeholders={placeholders}
                spintaxes={spintaxes}
                newStepData={newStepData}
                setNewStepData={setNewStepData}
            />
            {editModalOpen && selectedStep && (
                <EditStepModal
                    isOpen={editModalOpen}
                    onClose={() => setEditModalOpen(false)}
                    stepData={selectedStep}
                    placeholders={placeholders}
                    spintaxes={spintaxes}
                    updateStep={updateStep}
                    deleteStep={deleteStep}
                />
            )}

            <EditWorkflowModal
                isOpen={editWorkflowModalOpen}
                onClose={() => setEditWorkflowModalOpen(false)}
                workflow={workflow}
                voices={voices}
                callingNumbers={calling_numbers}
                textingNumbers={texting_numbers}
                numberPools={numberPools}
                handleSubmit={handleFormSubmit}
                handleChange={handleFormChange}
                data={workflowData}
                setData={setWorkflowData}
                errors={errors}
            />
        </AuthenticatedLayout>
    );
}
