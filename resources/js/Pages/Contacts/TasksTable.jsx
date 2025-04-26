import Pagination from "@/Components/Pagination";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";
import TableHeading from "@/Components/TableHeading";
import { Link, router } from "@inertiajs/react";
import { useLocation } from 'react-router-dom';

export default function TasksTable({
    contacts,
    success,
    workflow_id,
    queryParams = {},
    hideProjectColumn = false,
    statuses
}) {
    console.log(statuses);
    queryParams = queryParams || {};
    console.log(queryParams);
    const location = useLocation(); // Get the current location
    const currentParams = new URLSearchParams(location.search); // Parse current query parameters

    // Generate the export URL with the workflow ID and existing parameters
    const exportUrl = `/contacts/export/${workflow_id}?${currentParams.toString()}`;

    const searchFieldChanged = (name, value) => {
        const updatedParams = { ...queryParams };

        if (value) {
            updatedParams[name] = value;
        } else {
            delete updatedParams[name];
        }

        router.get(route("contacts.index", workflow_id), updatedParams);
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

        router.get(route("contacts.index", workflow_id), updatedParams);
    };

    return (
        <>
            {success && (
                <div className="bg-emerald-500 py-2 px-4 text-white rounded mb-4 shadow-md">
                    {success}
                </div>
            )}
            <div className="overflow-x-auto shadow-md rounded-lg">
                <table className="w-full text-sm text-left rtl:text-right border-collapse border border-gray-200">
                    <thead className="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-300">
                        <tr className="text-nowrap">
                            <TableHeading
                                name="id"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                ID
                            </TableHeading>
                            <TableHeading
                                name="Contact_name"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Contact Name
                            </TableHeading>
                            <TableHeading
                                name="phone"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Phone
                            </TableHeading>
                            <TableHeading
                                name="response"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Response
                            </TableHeading>
                            <TableHeading
                                name="current_step"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Current Step
                            </TableHeading>
                            <TableHeading
                                name="status"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Current Step Status
                            </TableHeading>
                            <TableHeading
                                name="can_send_after"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Next Step on
                            </TableHeading>

                            <TableHeading
                                name="created_at"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Create Date
                            </TableHeading>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <thead className="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr className="text-nowrap">
                            <th className="px-3 py-3"></th>
                            <th className="px-3 py-3">
                                <TextInput
                                    className="w-full"
                                    defaultValue={
                                        queryParams.contact_name || ""
                                    }
                                    placeholder="contact name"
                                    onBlur={(e) =>
                                        searchFieldChanged(
                                            "contact_name",
                                            e.target.value
                                        )
                                    }
                                    onKeyPress={(e) =>
                                        onKeyPress("contact_name", e)
                                    }
                                />
                            </th>
                            <th className="px-3 py-3">
                                <TextInput
                                    className="w-full"
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
                            <th className="px-3 py-3">
                                <SelectInput
                                    className="w-full"
                                    defaultValue={queryParams.response || ""}
                                    onChange={(e) =>
                                        searchFieldChanged(
                                            "response",
                                            e.target.value
                                        )
                                    }
                                >
                                    <option value="">Select Status</option>
                                    <option value="yes">YES</option>
                                    <option value="no">NO</option>
                                </SelectInput>
                            </th>

                            {!hideProjectColumn && (
                                <th className="px-3 py-3"></th>
                            )}
                            <th className="px-3 py-3">
                                <SelectInput
                                    className="w-full"
                                    defaultValue={
                                        queryParams.queaue_status || ""
                                    }
                                    onChange={(e) =>
                                        searchFieldChanged(
                                            "queaue_status",
                                            e.target.value
                                        )
                                    }
                                >
                                    <option value="">Select Status</option>
                                    {statuses.map((status, index) => (
                                        <option key={index} value={status}>
                                            {status}
                                        </option>
                                    ))}
                                </SelectInput>
                            </th>
                            <th className="px-3 py-3"></th>
                            <th className="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {contacts.data.map((contact) => (
                            <tr
                                className="bg-white text-gray-800 border-b hover:bg-gray-50 transition duration-150"
                                key={contact.id}
                            >
                                <td className="px-3 py-2">{contact.id}</td>
                                {!hideProjectColumn && (
                                    <td className="px-3 py-2">
                                        {contact.contact_name}
                                    </td>
                                )}

                                <td className="px-3 py-2">
                                    <span className="px-2 py-1 rounded text-nowrap bg-gray-100">
                                        {contact.phone}
                                    </span>
                                </td>
                                <td className="px-3 py-2 text-nowrap">
                                    {contact.response}
                                </td>
                                <td className="px-3 py-2 text-nowrap">
                                    {contact.current_step}
                                </td>
                                <td className="px-3 py-2 text-nowrap">
                                    {contact.status}
                                </td>
                                <td className="px-3 py-2 text-nowrap">
                                    {contact.can_send_after}
                                </td>
                                <td className="px-3 py-2">
                                    {contact.created_at}
                                </td>
                                <td className="">
                                    <div className="flex mb-1">
                                        <Link
                                            href={route(
                                                "mark-lead",
                                                contact.id
                                            )}
                                            className={`bg-blue-600 hover:bg-blue-300 text-white text-nowrap mr-1 ${contact.valid_lead == 1
                                                ? "bg-red-600 hover:bg-red-300"
                                                : ""
                                                }`}
                                        >
                                            {contact.valid_lead == 1
                                                ? "Unmark as lead"
                                                : "Mark Lead"}
                                        </Link>{" "}
                                        <Link
                                            href={route(
                                                "mark-offer",
                                                contact.id
                                            )}
                                            className={`bg-blue-600 hover:bg-blue-300 text-white text-nowrap mr-1 ${contact.offer_made == 1
                                                ? "bg-red-600 hover:bg-red-300"
                                                : ""
                                                }`}
                                        >
                                            {contact.offer_made == 1
                                                ? "Unmark as offer"
                                                : "Mark offer"}
                                        </Link>
                                        <Link
                                            href={route(
                                                "execute-contract",
                                                contact.id
                                            )}
                                            className={`bg-blue-600 hover:bg-blue-300 text-white text-nowrap mr-1 ${contact.contract_executed == 1
                                                ? "bg-red-600 hover:bg-red-300"
                                                : ""
                                                }`}
                                        >
                                            {contact.contract_executed == 1
                                                ? "Unxecute contract"
                                                : "execute contract"}
                                        </Link>
                                    </div>
                                    <div className="flex">
                                        <Link
                                            href={route(
                                                "close-deal",
                                                contact.id
                                            )}
                                            className={`bg-blue-600 hover:bg-blue-300 text-white text-nowrap mr-1 ${contact.deal_closed == 1
                                                ? "bg-red-600 hover:bg-red-300"
                                                : ""
                                                }`}
                                        >
                                            {contact.deal_closed == 1
                                                ? "Unclose Deal"
                                                : "Close Deal"}{" "}

                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                <div className="flex justify-center my-4">
                    <a
                        href={exportUrl}
                        className="text-center mt-2 w-1/4 text-white bg-green-600 hover:bg-green-500 px-4 py-2 rounded-lg shadow-md transition duration-150"
                    >
                        Export Contacts with No Response
                    </a>
                </div>
            </div>

            <Pagination
                links={contacts.meta.links}
                queryParams={queryParams}
                className="mt-4"
            />
        </>
    );
}
