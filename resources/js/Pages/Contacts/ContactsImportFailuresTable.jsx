
import Pagination from "@/Components/Pagination";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";
import TableHeading from "@/Components/TableHeading";
import { Link, router } from "@inertiajs/react";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
dayjs.extend(relativeTime);

export default function ContactsImportFailuresTable({
    failures,
    group_id,
    queryParams = {},
    success,
    error,
    hideProjectColumn = false,
}) {
    queryParams = queryParams || {};
    console.log(failures);

    const searchFieldChanged = (name, value) => {
        const updatedParams = { ...queryParams };

        if (value) {
            updatedParams[name] = value;
        } else {
            delete updatedParams[name];
        }

        router.get(route("get_group_contacts", group_id), updatedParams);
    };

    const onKeyPress = (name, e) => {
        if (e.key !== "Enter") return;
        searchFieldChanged(name, e.target.value);
    };

    const sortChanged = (name) => {
        const updatedParams = { ...queryParams };

        if (name === updatedParams.sort_field) {
            updatedParams.sort_direction =
                updatedParams.sort_direction === "asc" ? "desc" : "asc";
        } else {
            updatedParams.sort_field = name;
            updatedParams.sort_direction = "asc";
        }

        router.get(route("get_group_contacts", group_id), updatedParams);
    };

    return (
        <>
            {success && (
                <div className="bg-emerald-500 py-2 px-4 text-white rounded mb-4 shadow-md">
                    {success}
                </div>
            )}
            {error && (
                <div className="bg-red-500 py-2 px-4 text-white rounded mb-4 shadow-md">
                    {error}
                </div>
            )}
            <div className="overflow-x-auto shadow-lg rounded-xl bg-white">
                <table className="w-full text-left border-collapse bg-white">
                    <thead>
                        <tr className="bg-gray-100 border-b border-gray-200">
                            <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">ID</th>
                            <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">Phone</th>
                            <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">Contact Name</th>
                            <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">Failure Reason</th>
                            <th className="px-6 py-3 text-black font-semibold text-sm tracking-wider">Create Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        {failures.data.map((failure, index) => (
                            <tr
                                key={failure.id}
                                className={
                                    index % 2 === 0
                                        ? 'bg-white hover:bg-gray-50 transition duration-200 ease-in-out'
                                        : 'bg-gray-50 hover:bg-gray-100 transition duration-200 ease-in-out'
                                }
                            >
                                <td className="px-6 py-3 text-black border-b border-gray-200 align-middle">{failure.id}</td>
                                <td className="px-6 py-3 text-black border-b border-gray-200 align-middle">{failure.phone}</td>
                                <td className="px-6 py-3 text-black border-b border-gray-200 align-middle">{failure.contact_name}</td>
                                <td className="px-6 py-3 border-b border-gray-200 align-middle"><span className="inline-block bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium">{failure.error}</span></td>
                                <td className="px-6 py-3 text-black border-b border-gray-200 align-middle">{dayjs(failure.created_at).fromNow()}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination
                links={failures.links}
                queryParams={queryParams}
                className="mt-4"
            />
        </>
    );
}
