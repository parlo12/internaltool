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
                <div className="bg-emerald-500 py-2 px-4 text-white rounded mb-4 text-sm font-medium">
                    {success}
                </div>
            )}
            <div className="overflow-x-auto shadow-md rounded-lg">
                <table className="w-full text-sm text-left text-gray-700">
                    <thead className="text-xs text-gray-600 uppercase bg-gray-100">
                        <tr>
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
                        </tr>
                    </thead>
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-3 py-3"></th>
                            <th className="px-3 py-3">
                                <TextInput
                                    className="w-full border-gray-300 rounded-md"
                                    defaultValue={queryParams.contact_name || ""}
                                    placeholder="Contact Name"
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
                                    className="w-full border-gray-300 rounded-md"
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
                                    className="w-full border-gray-300 rounded-md"
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
                                    className="w-full border-gray-300 rounded-md"
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
                        </tr>
                    </thead>
                    <tbody>
                        {contacts.data.map((contact) => (
                            <tr
                                className="bg-white text-gray-700 border-b hover:bg-gray-50"
                                key={contact.id}
                            >
                                <td className="px-3 py-2">{contact.id}</td>
                                {!hideProjectColumn && (
                                    <td className="px-3 py-2">
                                        {contact.contact_name}
                                    </td>
                                )}
                                <td className="px-3 py-2">
                                    <span className="px-2 py-1 rounded bg-gray-100">
                                        {contact.phone}
                                    </span>
                                </td>
                                <td className="px-3 py-2">{contact.response}</td>
                                <td className="px-3 py-2">{contact.current_step}</td>
                                <td className="px-3 py-2">{contact.status}</td>
                                <td className="px-3 py-2">{contact.can_send_after}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                <div className="flex justify-center my-4">
                    <a
                        href={exportUrl}
                        className="px-4 py-2 text-white bg-green-600 hover:bg-green-500 rounded-md text-sm"
                    >
                        Export Contacts with No Response
                    </a>
                </div>
            </div>
            <Pagination links={contacts.meta.links} queryParams={queryParams} />
        </>
    );
}
