import Pagination from "@/Components/Pagination";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";
import TableHeading from "@/Components/TableHeading";
import { Link, router } from "@inertiajs/react";

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
            <div className="overflow-x-auto shadow-md rounded-lg bg-onyx">
                <table className="w-full text-left border-collapse">
                    <thead>
                        <tr className="bg-[#1A1A1A]">
                            <th className="px-4 py-2 text-[#FAFAFA]">ID</th>
                            <th className="px-4 py-2 text-[#FAFAFA]">Phone</th>
                            <th className="px-4 py-2 text-[#FAFAFA]">First Name</th>
                            <th className="px-4 py-2 text-[#FAFAFA]">Last Name</th>
                            <th className="px-4 py-2 text-[#FAFAFA]">Failure Reason</th>
                            <th className="px-4 py-2 text-[#FAFAFA]">Create Date</th>
                        </tr>
                    </thead>
                    {/* <thead className="text-xs text-dark-gray uppercase bg-charcoal">
                        <tr className="text-nowrap">
                            <th className="px-3 py-3"></th>
                            <th className="px-3 py-3">
                                <TextInput
                                    className="w-full bg-dark-gray text-black placeholder-gray-400 border border-dim-gray rounded"
                                    defaultValue={
                                        queryParams.contact_name || ""
                                    }
                                    placeholder="Contact Name"
                                    onBlur={(e) =>
                                        searchFieldChanged(
                                            "name",
                                            e.target.value
                                        )
                                    }
                                    onKeyPress={(e) =>
                                        onKeyPress("name", e)
                                    }
                                />
                            </th>
                            <th className="px-3 py-3">
                                <TextInput
                                    className="w-full bg-dark-gray text-black placeholder-gray-400 border border-dim-gray rounded"
                                    defaultValue={queryParams.phone || ""}
                                    placeholder="Phone"
                                    onBlur={(e) =>
                                        searchFieldChanged(
                                            "phone",
                                            e.target.value
                                        )
                                    }
                                    onKeyPress={(e) => onKeyPress("phone", e)}
                                />
                            </th>
                            {!hideProjectColumn && (
                                <th className="px-3 py-3"></th>
                            )}
                        </tr>
                    </thead> */}
                    <tbody>
                        {failures.data.map((failure, index) => (
                            <tr
                                key={failure.id}
                                className={`${index % 2 === 0 ? 'bg-[#262626]' : 'bg-[#2E2E2E]'
                                    } hover:bg-[#1B1B1B] transition duration-200 ease-in-out`}
                            >
                                <td className="px-4 py-2 text-[#FAFAFA]">{failure.id}</td>
                                <td className="px-4 py-2 text-[#FAFAFA]">{failure.phone}</td>
                                <td className="px-4 py-2 text-[#FAFAFA]">{failure.first_name}</td>
                                <td className="px-4 py-2 text-[#FAFAFA]">{failure.last_name}</td>
                                <td className=" border-b px-4 py-2 bg-red-600 text-[#FAFAFA]">{failure.error}</td>                                <td className="px-4 py-2 text-[#FAFAFA]">{failure.created_at}</td>
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
