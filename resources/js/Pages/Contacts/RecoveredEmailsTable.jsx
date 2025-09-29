import Pagination from "@/Components/Pagination";
import dayjs from "dayjs";

export default function RecoveredEmailsTable({ recoveredEmails, exportUrl }) {
    return (
        <div className="overflow-x-auto shadow-lg rounded-xl bg-white mt-8">
            <h2 className="text-lg font-bold text-black px-6 py-4">Recovered Emails</h2>
            <table className="w-full text-left border-collapse bg-white">
                <thead>
                    <tr className="bg-gray-100 border-b border-gray-200">
                        <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">ID</th>
                        <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">Phone</th>
                        <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">Contact Name</th>
                        <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">Email</th>
                        <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">Create Date</th>
                    </tr>
                </thead>
                <tbody>
                    {recoveredEmails.data && recoveredEmails.data.length > 0 ? (
                        recoveredEmails.data.map((email) => (
                            <tr key={email.id} className="bg-white hover:bg-gray-50 transition duration-200 ease-in-out">
                                <td className="px-6 py-3 text-black border-b border-gray-200 align-middle">{email.id}</td>
                                <td className="px-6 py-3 text-black border-b border-gray-200 align-middle">{email.phone}</td>
                                <td className="px-6 py-3 text-black border-b border-gray-200 align-middle">{email.contact_name}</td>
                                <td className="px-6 py-3 text-black border-b border-gray-200 align-middle">{email.email}</td>
                                <td className="px-6 py-3 text-black border-b border-gray-200 align-middle">{dayjs(email.created_at).format('MMM D, YYYY h:mm A')}</td>
                            </tr>
                        ))
                    ) : (
                        <tr>
                            <td colSpan={5} className="px-6 py-3 text-center text-gray-500">No recovered emails found.</td>
                        </tr>
                    )}
                </tbody>
            </table>
            <div className="flex justify-end my-4">
                <a
                    href={exportUrl}
                    className="text-center text-nowrap mt-2 text-white bg-green-600 hover:bg-green-500 px-4 py-2 rounded-lg shadow-md transition duration-150"
                    download
                >
                    Export Recovered Emails (CSV)
                </a>
            </div>
            {recoveredEmails && recoveredEmails.links && (
                <Pagination
                    links={recoveredEmails.links}
                    className="mt-4"
                />
            )}
        </div>
    );
}
