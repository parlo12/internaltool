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
import axios from "axios";

const ViewNumberPoolPopup = ({ showNumberPoolPopup, setShowNumberPoolPopup, data }) => {
    const [numberPoolData, setNumberPoolData] = useState(null);

    useEffect(() => {
        if (!showNumberPoolPopup || !data.number_pool_id) return;
        const url = `/get_number_pool/${data.number_pool_id}`;

        axios.get(url)
            .then((response) => {
                setNumberPoolData(response.data);
            })
            .catch((error) => {
                console.error("Error fetching number pool:", error);
            });
    }, [showNumberPoolPopup, data.number_pool_id]);

    if (!showNumberPoolPopup) return null;

    return (
        <div className="fixed inset-0 flex items-center justify-center z-10">
            <div className="bg-white p-4 rounded shadow-lg">
                <div>
                    {numberPoolData && (
                        <div className="w-full mx-auto ">
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className=" bg-white border-b border-gray-200 overflow-x-auto">
                                    <div className="flex">
                                        <div className="pr-4">Pool Name:</div>
                                        <div>{numberPoolData.numberPool.pool_name}</div>
                                    </div>
                                    <div className="bg-white rounded-lg shadow-md w-full">
                                        <div className=" bg-white border-b border-gray-200 overflow-x-auto">
                                            <table className="divide-y divide-gray-200">
                                                <thead>
                                                    <tr>
                                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            #
                                                        </th>
                                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Number
                                                        </th>
                                                        <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Purpose
                                                        </th>
                                                        <th className="px-6 py-3 bg-gray-50">Provider</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="bg-white divide-y divide-gray-200">
                                                    {numberPoolData.numbers?.length > 0 ? (
                                                        numberPoolData.numbers.map((number, index) => (
                                                            <tr key={number.id}>
                                                                <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                                    {index + 1}
                                                                </td>
                                                                <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                                    {number.phone_number}
                                                                </td>
                                                                <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                                    {number.purpose}
                                                                </td>
                                                                <td className="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                                                    {number.provider}
                                                                </td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr>
                                                            <td colSpan="4" className="px-6 py-3 text-center text-gray-500">
                                                                No numbers available
                                                            </td>
                                                        </tr>
                                                    )}
                                                </tbody>
                                            </table>

                                            {/* Remove pagination if not needed */}
                                            {/* <Pagination links={numberPoolData.meta?.links} /> */}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
                <div className="mt-4 flex justify-end">
                    <PrimaryButton
                        type="button"
                        onClick={() => setShowNumberPoolPopup(false)}
                        className="mr-2"
                    >
                        Cancel
                    </PrimaryButton>
                </div>
            </div>
        </div>
    );
};

export default ViewNumberPoolPopup;
