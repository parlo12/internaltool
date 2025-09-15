import React, { useState, useEffect } from "react";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SelectInput from "./SelectInput";
import TextAreaInput from "./TextAreaInput";
import TextInput from "./TextInput";
import InputError from "./InputError";
import Tooltip from "./Tooltip";

const UpdateNumberPoolPopup = ({
    isOpen,
    onClose,
    submitNumberPoolUpdate,
    data,
    setData,
    showUpdateNumberPoolPopup
}) => {
    useEffect(() => {
        const url = `/get_number_pool/${data.number_pool_id}`;

        // Make the GET request
        axios
            .get(url)
            .then((response) => {
                // Update the state with the response data
                setData(response.data.numberPool);
            })
            .catch((error) => {
                // Update the state with the error
                // setError("There was an error making the request!");
                console.error(error);
            });
    }, [showUpdateNumberPoolPopup]);

    if (!showUpdateNumberPoolPopup) return null;
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
                    <div>Edit Number Pool</div>

                    <form
                        onSubmit={submitNumberPoolUpdate}
                        className="space-y-4"
                    >
                        {data && (
                            <>
                                <div>
                                    <InputLabel className="block text-sm font-medium text-gray-700">
                                        Pool Name
                                    </InputLabel>
                                    <TextInput
                                        name="pool_name"
                                        value={data.pool_name}
                                        onChange={(e) =>
                                            setData({
                                                ...data,
                                                pool_name:
                                                    e.target.value,
                                            })
                                        }
                                        className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="Enter Pool Name"
                                        required
                                    ></TextInput>
                                </div>
                                <div className="flex items-center mb-4">
                                    <div className="mr-2 w-1/3">
                                        <InputLabel
                                            forInput="pool_messages"
                                            value="Send"
                                        />
                                        <input
                                            type="number"
                                            min="1" // Enforce minimum wait time limit
                                            name="pool_messages"
                                            value={data.pool_messages}
                                            required
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    pool_messages:
                                                        e.target.value,
                                                })} className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                        />
                                    </div>
                                    <div className="mr-2 w-1/3">
                                        <InputLabel
                                            forInput="pool"
                                            value="Every"
                                        />
                                        <input
                                            type="number"
                                            min="1" // Enforce minimum wait time limit
                                            name="pool_time"
                                            value={data.pool_time}
                                            required
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    pool_time:
                                                        e.target.value,
                                                })}
                                            className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            forInput="pool_time_units"
                                            value="Units"
                                        />
                                        <select
                                            name="pool_time_units"
                                            onChange={(e) =>
                                                setData({
                                                    ...data,
                                                    pool_time_units:
                                                        e.target.value,
                                                })}
                                            className="mt-1 block w-full border border-black rounded-md shadow-sm text-center"
                                            value={data.pool_time_units}
                                        >
                                            <option value="minutes">Minutes</option>
                                            <option value="hours">Hours</option>
                                            <option value="days">Days</option>
                                        </select>
                                    </div>
                                </div>
                            </>
                        )}
                        <div>
                            <PrimaryButton
                                type="submit"
                                className="w-36 text-center bg-indigo-600 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Edit Number Pool
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default UpdateNumberPoolPopup;
