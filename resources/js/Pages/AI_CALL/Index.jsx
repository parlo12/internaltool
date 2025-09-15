import { Link,Head } from "@inertiajs/react";
import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Index({ success, error, ai_calls,auth }) {
    const baseURL = import.meta.env.VITE_APP_URL;
    return ( // Add the return keyword
        <AuthenticatedLayout user={auth.user}>
        <Head title="Assistants" />
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

            {/* Assistants Table */}
            <div
  className="overflow-y-auto h-screen overflow-x-auto mx-auto my-4 w-full max-w-8xl p-6 bg-white rounded-lg shadow-md"
>
  <table className="min-w-full border-collapse border border-gray-200">
    <thead>
      <tr className="bg-gray-100">
        <th className="border border-gray-300 px-4 py-2 text-left">#</th>
        <th className="border border-gray-300 px-4 py-2 text-left">call_id</th>
        <th className="border border-gray-300 px-4 py-2 text-left">Calling Phone</th>
        <th className="border border-gray-300 px-4 py-2 text-left">Called Phone</th>
        <th className="border border-gray-300 px-4 py-2 text-left">Call Summary</th>
      </tr>
    </thead>
    <tbody>
      {ai_calls.length > 0 ? (
        ai_calls.map((ai_call, index) => (
          <tr
            key={ai_call.id}
            className={index % 2 === 0 ? "bg-white" : "bg-gray-50"}
          >
            <td className="border border-gray-300 px-4 py-2">{index + 1}</td>
            <td className="border border-gray-300 px-4 py-2">{ai_call.call_id}</td>
            <td className="border border-gray-300 px-4 py-2">{ai_call.calling_phone}</td>
            <td className="border border-gray-300 px-4 py-2">{ai_call.called_phone}</td>
            <td className="border border-gray-300 px-4 py-2">{ai_call.call_summary}</td>
          </tr>
        ))
      ) : (
        <tr>
          <td
            colSpan="6"
            className="border border-gray-300 px-4 py-2 text-center"
          >
            No ai calls found.
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
