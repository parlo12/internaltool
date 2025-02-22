import { Link, Head } from "@inertiajs/react";
import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Index({ success, error, underContracts, auth }) {
    console.log(underContracts)
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Under Contracts" />
            <div className="container mx-auto px-4 py-8">
                {/* Success and Error Messages */}
                {success && (
                    <div className="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded">
                        {success}
                    </div>
                )}
                {error && (
                    <div className="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded">
                        {error}
                    </div>
                )}

                <div className="overflow-x-auto mx-auto my-4 w-full max-w-8xl p-6 bg-white rounded-lg shadow-md">
                    <table className="min-w-full border-collapse border border-gray-200">
                        <thead>
                            <tr className="bg-gray-100">
                                <th className="border border-gray-300 px-4 py-2 text-left">#</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Phone</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Contact Name</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Workflow ID</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Organisation ID</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">User ID</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Zipcode</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">State</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">City</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Address</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Offer</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Email</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Age</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Gender</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Lead Score</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Agent</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Novation</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Creative Price</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Monthly</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">Downpayment</th>
                                <th className="border border-gray-300 px-4 py-2 text-left">messages</th>

                            </tr>
                        </thead>
                        <tbody>
                            {underContracts.length > 0 ? (
                                underContracts.map((undercontract, index) => (
                                    <tr key={undercontract.id} className={index % 2 === 0 ? "bg-white" : "bg-gray-50"}>
                                        <td className="border border-gray-300 px-4 py-2">{index + 1}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.phone}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.contact_name}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.workflow_id}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.organisation_id}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.user_id}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.zipcode}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.state}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.city}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.address}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.offer}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.email}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.age}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.gender}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.lead_score}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.agent}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.novation}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.creative_price}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.monthly}</td>
                                        <td className="border border-gray-300 px-4 py-2">{undercontract.downpayment}</td>
                                        <td className="border border-gray-300 px-4 py-2">
                                            <button
                                                onClick={() => handleShowMessages(undercontract.messages)}
                                                className="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-800"
                                            >
                                                View Messages
                                            </button>
                                        </td>

                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="21" className="border border-gray-300 px-4 py-2 text-center">
                                        No undercontracts found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
            {/* Message Modal */}
            {isModalOpen && (
                <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                    <div className="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
                        <h2 className="text-lg font-semibold mb-4">Message Trail</h2>
                        <div className="max-h-60 overflow-y-auto">
                            {selectedMessages.length > 0 ? (
                                selectedMessages.map((msg, i) => (
                                    <div key={i} className="mb-2 p-2 border-b">
                                        <p><strong>Message:</strong> {msg.message}</p>
                                        <p><strong>Sent By:</strong> {msg.send_by}</p>
                                        <p><strong>Time:</strong> {msg.created_at}</p>
                                    </div>
                                ))
                            ) : (
                                <p>No messages available.</p>
                            )}
                        </div>
                        <button
                            onClick={() => setIsModalOpen(false)}
                            className="mt-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700"
                        >
                            Close
                        </button>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
