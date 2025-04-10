import React, { useState } from "react";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import { Head, Link, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import TextInput from "@/Components/TextInput";
import { router } from "@inertiajs/react";
import Pagination from "@/Components/Pagination";

export default function Create({
    success,
    auth,
    textsSent,
    callsSent,
    cancelledContracts,
    closedDeals,
    executedContracts,
    offers,
    validLeads,
    queryParams,
    totalCost,
    zipcodes,
    cities,
    states,
    agents,
    sending_numbers,
    AICalls
}) {

    console.log(agents);

    const { data, setData, get, errors, processing } = useForm({
        filter: "",
        city: "",
        state: "",
        zipcode: "",
        agent: "",
        response: "",
        marketing_channel: "",
        sending_number: ""
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        router.get(route("workflow-reports.index"), data);
    };

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create workflow" />
            <div className="container min-h-screen mx-auto">
                <div className="w-full p-2">
                    {success && (
                        <div className="bg-green-500 text-center text-white relative">
                            {success}
                        </div>
                    )}
                    <div className="text-2xl text-center">WorkFlow Reports</div>

                    <div className=" flex flex-col items-center justify-center ">
                        <form
                            onSubmit={handleSubmit}
                            className="mb-4 mx-auto flex flex-col justify-center"
                        >
                            <div className="flex space-x-4">
                                <div>
                                    <InputLabel
                                        htmlFor="filter"
                                        value="Filter by time period"
                                    />
                                    <select
                                        name="filter"
                                        value={data.filter}
                                        onChange={handleChange}
                                        className="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Select a Time Period</option>
                                        <option value="today">Today</option>
                                        <option value="this_week">This Week</option>
                                        <option value="this_month">This Month</option>
                                        <option value="last_3_months">
                                            Last 3 Months
                                        </option>
                                        <option value="last_6_months">
                                            Last 6 Months
                                        </option>
                                        <option value="this_year">This Year</option>
                                    </select>
                                    <InputLabel
                                        htmlFor="zipcode"
                                        value="Filter by zipcode"
                                    />
                                    <input
                                        type="text"
                                        name="zipcode"
                                        value={data.zipcode}
                                        onChange={handleChange}
                                        className="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        placeholder="Enter a Zipcode"
                                    />

                                    <InputLabel
                                        htmlFor="city"
                                        value="Filter by city"
                                    />
                                    <input
                                        type="text"
                                        name="city"
                                        value={data.city}
                                        onChange={handleChange}
                                        className="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        placeholder="Enter a city"
                                    />

                                    <InputLabel
                                        htmlFor="state"
                                        value="Filter by state"
                                    />
                                    <select
                                        name="state"
                                        value={data.state}
                                        onChange={handleChange}
                                        className="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Select a State</option>
                                        {Object.values(states).map((state, index) => (
                                            <option key={index} value={state}>
                                                {state}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <InputLabel
                                        htmlFor="agent"
                                        value="Filter by agent"
                                    />
                                    <select
                                        name="agent"
                                        value={data.agent}
                                        onChange={handleChange}
                                        className="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Select a Sales Rep</option>
                                        {agents.map((agent, index) => (
                                            <option key={index} value={agent.id}>
                                                {agent.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputLabel
                                        htmlFor="marketing_channel"
                                        value="Filter by marketing channel"
                                    />
                                    <select
                                        name="marketing_channel"
                                        value={data.marketing_channel}
                                        onChange={handleChange}
                                        className="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Select a Marketing Channel</option>
                                        <option value="SMS">SMS</option>
                                        <option value="VoiceMMS">VoiceMMS</option>
                                        <option value="VoiceMail">VoiceMail</option>
                                        <option value="VoiceCall">VoiceCall</option>
                                    </select>
                                    <InputLabel
                                        htmlFor="response"
                                        value="Filter by response"
                                    />
                                    <select
                                        name="response"
                                        value={data.response}
                                        onChange={handleChange}
                                        className="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Select a Response</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                    <InputLabel
                                        htmlFor="sending_number"
                                        value="Filter by sending number"
                                    />
                                    <select
                                        name="sending_number"
                                        value={data.sending_number}
                                        onChange={handleChange}
                                        className="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Select a Sending Number</option>
                                        {Object.values(sending_numbers).map((sending_number, index) => (
                                            <option key={index} value={sending_number}>
                                                {sending_number}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            <div className="w-full mx-auto flex justify-center">
                                <PrimaryButton
                                    className="mt-4"
                                    processing={processing}
                                >
                                    Filter
                                </PrimaryButton>
                            </div>

                        </form>
                        <div className="text-2xl mb-2">
                            The cost is sending SMS and calls is<span className="text-green-500 ">
                                &nbsp;{totalCost}$ &nbsp;
                            </span>

                            {queryParams && (
                                <div className="text-sm">
                                    For these parameters: <br />
                                    Time Period: {queryParams.filter ? queryParams.filter : "All Time"}&nbsp;
                                    State: {queryParams.state ? queryParams.state : "All states"}&nbsp;
                                    City: {queryParams.city ? queryParams.city : "All Cities"}&nbsp;
                                    Zipcode: {queryParams.zipcode ? queryParams.zipcode : "All Zipcodes"} &nbsp;
                                    Sales Rep: {
                                        queryParams.agent
                                            ? agents.find((user) => user.id === parseInt(queryParams.agent))?.name || "Unknown Sales Rep"
                                            : "All Sales Rep"
                                    }
                                    <br />

                                    Sending Number: {queryParams.sending_number ? queryParams.sending_number : "All Sending Numbers"} &nbsp;
                                    Marketing Channel: {queryParams.marketing_channel ? queryParams.marketing_channel : "All Marketing Channels"} &nbsp;
                                    Response: {queryParams.response ? queryParams.response : "YES/NO"} &nbsp;

                                </div>
                            )}


                        </div>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 w-full">
                            <div className="table-container">
                                <div>
                                    A total of{" "}
                                    <span className="text-green-500 ">
                                        {textsSent.meta.total}&nbsp;
                                    </span>
                                    Text Sent &nbsp;
                                    {/* {queryParams && (
                                        <span className="text-green-500 ">
                                            {queryParams.filter}
                                        </span>
                                    )} */}
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full bg-white border border-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-2 border-b text-nowrap">Contact Name</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Phone</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Cost</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Zipcode</th>
                                                <th className="px-4 py-2 border-b text-nowrap">City</th>
                                                <th className="px-4 py-2 border-b text-nowrap">State</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Marketing Channel</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Response</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Sending Number</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Date Sent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {textsSent.data.map((textSent) => (
                                                <tr key={textSent.id}>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.name}</td>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.phone}</td>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.cost}</td>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.zipcode}</td>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.city}</td>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.state}</td>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.marketing_channel}</td>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.response}</td>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.sending_number}</td>
                                                    <td className="px-4 py-2 border-b text-nowrap">{textSent.created_at}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <Pagination
                                    links={textsSent.meta.links}
                                    queryParams={queryParams}
                                />
                            </div>
                            {/* <div className="table-container">
                                <div>
                                    A total of{" "}
                                    <span className="text-green-500 ">
                                        {cancelledContracts.meta.total}&nbsp;
                                    </span>
                                    Contracts Cancelled &nbsp;
                                    {queryParams && (
                                        <span className="text-green-500 ">
                                            {queryParams.filter}
                                        </span>
                                    )}
                                </div>
                                <table className="min-w-full bg-white border border-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-4 py-2 border-b">
                                                Contact Name
                                            </th>
                                            <th className="px-4 py-2 border-b">
                                                Phone
                                            </th>
                                            <th className="px-4 py-2 border-b">
                                                Date Sent
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {cancelledContracts.data.map(
                                            (cancelledContract) => (
                                                <tr>
                                                    <td className="px-4 py-2 border-b">
                                                        {cancelledContract.name}
                                                    </td>
                                                    <td className="px-4 py-2 border-b">
                                                        {
                                                            cancelledContract.phone
                                                        }
                                                    </td>
                                                    <td className="px-4 py-2 border-b">
                                                        {
                                                            cancelledContract.created_at
                                                        }
                                                    </td>
                                                </tr>
                                            )
                                        )}
                                        <tr></tr>
                                    </tbody>
                                </table>
                                <Pagination
                                    links={cancelledContracts.meta.links}
                                    queryParams={queryParams}
                                />
                            </div> */}

                            <div className="table-container">
                                <div>
                                    A total of{" "}
                                    <span className="text-green-500 ">
                                        {callsSent.meta.total}&nbsp;
                                    </span>
                                    Calls Sent &nbsp;
                                    {/* {queryParams && (
                                        <span className="text-green-500 ">
                                            {queryParams.filter}
                                        </span>
                                    )} */}
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full bg-white border border-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-2 border-b text-nowrap">Contact Name</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Phone</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Cost</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Zipcode</th>
                                                <th className="px-4 py-2 border-b text-nowrap">City</th>
                                                <th className="px-4 py-2 border-b text-nowrap">State</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Marketing Channel</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Response</th>
                                                <td className="px-4 py-2 border-b text-nowrap">Sending Number</td>
                                                <th className="px-4 py-2 border-b text-nowrap">Date Sent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {callsSent.data.map((callSent) => (
                                                <tr>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.name}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.phone}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.cost}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.zipcode}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.city}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.state}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.marketing_channel}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.response}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.sending_number}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {callSent.created_at}
                                                    </td>
                                                </tr>
                                            ))}
                                            <tr></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <Pagination
                                    links={callsSent.meta.links}
                                    queryParams={queryParams}
                                />
                            </div>
                            <div className="table-container">
                                <div>
                                    A total of{" "}
                                    <span className="text-green-500 ">
                                        {closedDeals.meta.total}&nbsp;
                                    </span>
                                    Deals Closed &nbsp;
                                    {/* {queryParams && (
                                        <span className="text-green-500 ">
                                            {queryParams.filter}
                                        </span>
                                    )} */}
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full bg-white border border-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-2 border-b text-nowrap">Contact Name</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Phone</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Zipcode</th>
                                                <th className="px-4 py-2 border-b text-nowrap">City</th>
                                                <th className="px-4 py-2 border-b text-nowrap">State</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Date Sent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {closedDeals.data.map((closedDeal) => (
                                                <tr>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {closedDeal.name}
                                                    </td>
                                                    <td className="px-4 py-2 border-b  text-nowrap">
                                                        {closedDeal.phone}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {closedDeal.zipcode}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {closedDeal.city}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {closedDeal.state}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {closedDeal.created_at}
                                                    </td>
                                                </tr>
                                            ))}
                                            <tr></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <Pagination
                                    links={closedDeals.meta.links}
                                    queryParams={queryParams}
                                />
                            </div>
                            <div className="table-container">
                                <div>
                                    A total of{" "}
                                    <span className="text-green-500 ">
                                        {executedContracts.meta.total}&nbsp;
                                    </span>
                                    Contracts executed &nbsp;
                                    {/* {queryParams && (
                                        <span className="text-green-500 ">
                                            {queryParams.filter}
                                        </span>
                                    )} */}
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full bg-white border border-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-2 border-b text-nowrap">Contact Name</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Phone</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Zipcode</th>
                                                <th className="px-4 py-2 border-b text-nowrap">City</th>
                                                <th className="px-4 py-2 border-b text-nowrap">State</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Date Sent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {executedContracts.data.map(
                                                (executedContract) => (
                                                    <tr>
                                                        <td className="px-4 py-2 border-b text-nowrap">
                                                            {executedContract.name}
                                                        </td>
                                                        <td className="px-4 py-2 border-b text-nowrap">
                                                            {executedContract.phone}
                                                        </td>
                                                        <td className="px-4 py-2 border-b text-nowrap">
                                                            {executedContract.zipcode}
                                                        </td>
                                                        <td className="px-4 py-2 border-b text-nowrap">
                                                            {executedContract.city}
                                                        </td>
                                                        <td className="px-4 py-2 border-b text-nowrap">
                                                            {executedContract.state}
                                                        </td>
                                                        <td className="px-4 py-2 border-b text-nowrap">
                                                            {executedContract.created_at}
                                                        </td>
                                                    </tr>
                                                )
                                            )}
                                            <tr></tr>
                                        </tbody>
                                    </table>
                                </div>

                                <Pagination
                                    links={executedContracts.meta.links}
                                    queryParams={queryParams}
                                />
                            </div>
                            <div className="table-container">
                                <div>
                                    A total of{" "}
                                    <span className="text-green-500 ">
                                        {offers.meta.total}&nbsp;
                                    </span>
                                    Offers Made &nbsp;
                                    {/* {queryParams && (
                                        <span className="text-green-500 ">
                                            {queryParams.filter}
                                        </span>
                                    )} */}
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full bg-white border border-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-2 border-b text-nowrap">Contact Name</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Phone</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Zipcode</th>
                                                <th className="px-4 py-2 border-b text-nowrap">City</th>
                                                <th className="px-4 py-2 border-b text-nowrap">State</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Date Sent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {offers.data.map((offer) => (
                                                <tr>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {offer.name}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {offer.phone}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {offer.zipcode}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {offer.city}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {offer.state}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {offer.created_at}
                                                    </td>
                                                </tr>
                                            ))}
                                            <tr></tr>
                                        </tbody>
                                    </table>
                                </div>

                                <Pagination
                                    links={validLeads.meta.links}
                                    queryParams={queryParams}
                                />
                            </div>
                            <div className="table-container">
                                <div>
                                    A total of{" "}
                                    <span className="text-green-500 ">
                                        {validLeads.meta.total}&nbsp;
                                    </span>
                                    Leads Generated &nbsp;
                                    {/* {queryParams && (
                                        <span className="text-green-500 ">
                                            {queryParams.filter}
                                        </span>
                                    )} */}
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full bg-white border border-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-2 border-b text-nowrap">Contact Name</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Phone</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Zipcode</th>
                                                <th className="px-4 py-2 border-b text-nowrap">City</th>
                                                <th className="px-4 py-2 border-b text-nowrap">State</th>
                                                <th className="px-4 py-2 border-b  text-nowrap">Date Sent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {validLeads.data.map((validLead) => (
                                                <tr>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {validLead.name}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {validLead.phone}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {validLead.zipcode}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {validLead.city}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {validLead.state}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {validLead.created_at}
                                                    </td>
                                                </tr>
                                            ))}
                                            <tr></tr>
                                        </tbody>
                                    </table>
                                </div>


                                <Pagination
                                    links={validLeads.meta.links}
                                    queryParams={queryParams}
                                />
                            </div>
                            <div className="table-container">
                                <div>
                                    A total of{" "}
                                    <span className="text-green-500 ">
                                        {AICalls.meta.total}&nbsp;
                                    </span>
                                    AI Calls made &nbsp;
                                    {/* {queryParams && (
                                        <span className="text-green-500 ">
                                            {queryParams.filter}
                                        </span>
                                    )} */}
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full bg-white border border-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-2 border-b text-nowrap">Contact Name</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Phone</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Cost</th>
                                                <th className="px-4 py-2 border-b text-nowrap">Zipcode</th>
                                                <th className="px-4 py-2 border-b text-nowrap">City</th>
                                                <th className="px-4 py-2 border-b text-nowrap">State</th>
                                                <th className="px-4 py-2 border-b  text-nowrap">Date Sent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {AICalls.data.map((AICall) => (
                                                <tr>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {AICall.name}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {AICall.phone}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {AICall.cost}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {AICall.zipcode}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {AICall.city}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {AICall.state}
                                                    </td>
                                                    <td className="px-4 py-2 border-b text-nowrap">
                                                        {AICall.created_at}
                                                    </td>
                                                </tr>
                                            ))}
                                            <tr></tr>
                                        </tbody>
                                    </table>
                                </div>


                                <Pagination
                                    links={AICalls.meta.links}
                                    queryParams={queryParams}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
