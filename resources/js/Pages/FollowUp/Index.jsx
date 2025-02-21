import { Link, Head } from "@inertiajs/react";
import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Index({ success, error, followUps, auth }) {
    console.log(followUps)
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Follow Ups" />
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
                            </tr>
                        </thead>
                        <tbody>
                            {followUps.length > 0 ? (
                                followUps.map((followup, index) => (
                                    <tr key={followup.id} className={index % 2 === 0 ? "bg-white" : "bg-gray-50"}>
                                        <td className="border border-gray-300 px-4 py-2">{index + 1}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.phone}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.contact_name}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.workflow_id}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.organisation_id}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.user_id}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.zipcode}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.state}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.city}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.address}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.offer}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.email}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.age}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.gender}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.lead_score}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.agent}</td>
                                        <td className="border border-gray-300 px-4 py-2">
                                            {followup.novation ? "Yes" : "No"}
                                        </td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.creative_price}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.monthly}</td>
                                        <td className="border border-gray-300 px-4 py-2">{followup.downpayment}</td>
                                        
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="21" className="border border-gray-300 px-4 py-2 text-center">
                                        No wrong numbers found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
