import Pagination from "@/Components/Pagination";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";
import TableHeading from "@/Components/TableHeading";
import { Link, router } from "@inertiajs/react";

export default function TasksTable({
    reports,
    success,
    queryParams = {},
    hideProjectColumn = false,
}) {
    queryParams = queryParams || {};

    const searchFieldChanged = (name, value) => {
        const updatedParams = { ...queryParams };

        if (value) {
            updatedParams[name] = value;
        } else {
            delete updatedParams[name];
        }

        router.get(route("reports.index"), updatedParams);
    };

    const onKeyPress = (name, e) => {
        if (e.key !== "Enter") return;
        searchFieldChanged(name, e.target.value);
    };

    const sortChanged = (name) => {
        const updatedParams = { ...queryParams };

        if (name === updatedParams.sort_field) {
            updatedParams.sort_direction = updatedParams.sort_direction === "asc" ? "desc" : "asc";
        } else {
            updatedParams.sort_field = name;
            updatedParams.sort_direction = "asc";
        }

        router.get(route("reports.index"), updatedParams);
    };

    return (
        <>
            {success && (
                <div className="bg-emerald-500 py-2 px-4 text-white rounded mb-4">
                    {success}
                </div>
            )}
            <div className="overflow-auto">
                <table className="w-full text-sm text-left rtl:text-right">
                    <thead className="text-xs text-black uppercase bg-gray-50 border-gray-500">
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
                                name="campaign_id"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Campaign ID
                            </TableHeading>
                        
                            <TableHeading
                                name="group_name"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Group Name
                            </TableHeading>

                            <TableHeading
                                name="call_status"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Call Status
                            </TableHeading>

                            <TableHeading
                                name="created_at"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Create Date
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
                                name="contact_name"
                                sort_field={queryParams.sort_field}
                                sort_direction={queryParams.sort_direction}
                                sortChanged={sortChanged}
                            >
                                Contact Name
                            </TableHeading>
                        </tr>
                    </thead>
                    <thead className="text-xs text-black uppercase bg-gray-50">
                        <tr className="text-nowrap">
                            <th className="px-3 py-3"></th>
                            <th className="px-3 py-3">
                                <TextInput
                                    className="w-full"
                                    defaultValue={queryParams.campaign_id || ""}
                                    placeholder="campaign id"
                                    onBlur={(e) =>
                                        searchFieldChanged(
                                            "campaign_id",
                                            e.target.value
                                        )
                                    }
                                    onKeyPress={(e) => onKeyPress("campaign_id", e)}
                                />
                            </th>   
                            <th className="px-3 py-3">
                                <TextInput
                                    className="w-full"
                                    defaultValue={queryParams.group_name || ""}
                                    placeholder="Group Name"
                                    onBlur={(e) =>
                                        searchFieldChanged(
                                            "group_name",
                                            e.target.value
                                        )
                                    }
                                    onKeyPress={(e) => onKeyPress("group_name", e)}
                                />
                            </th>      
                            <th className="px-3 py-3">
                                <SelectInput
                                    className="w-full"
                                    defaultValue={queryParams.call_status || ""}
                                    onChange={(e) =>
                                        searchFieldChanged(
                                            "call_status",
                                            e.target.value
                                        )
                                    }
                                >
                                    <option value="">Select Status</option>
                                    <option value="ANSWERED">ANSWERED</option>
                                    <option value="FAILED">FAILED</option>
                                    <option value="CALL_TRANSFERRED">CALL_TRANSFERRED</option>
                                    <option value="RECORDING_PLAYED">RECORDING_PLAYED</option>
                                    <option value="VOICEMAIL_LEFT">VOICEMAIL_LEFT</option>
                                    <option value="RECORDING_PLAYED_NOT_TRANSFERRED">RECORDING_PLAYED_NOT_TRANSFERRED</option>
                                    <option value="SUCCESSFUL">SUCCESSFUL</option>
                                    <option value="QUEUED">QUEUED</option>

                                </SelectInput>
                            </th>                  
                            {!hideProjectColumn && (
                                <th className="px-3 py-3"></th>
                            )}
                            <th className="px-3 py-3"></th>
                            <th className="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {reports.data.map((report) => (
                            <tr
                                className="bg-white text-black border-b"
                                key={report.id}
                            >
                                <td className="px-3 py-2">{report.id}</td>
                                {!hideProjectColumn && (
                                    <td className="px-3 py-2">
                                        {report.campaign_id}
                                    </td>
                                )}
                                {!hideProjectColumn && (
                                    <td className="px-3 py-2">
                                        {report.group_name}
                                    </td>
                                )}
                                
                                <td className="px-3 py-2">
                                    <span className="px-2 py-1 rounded text-nowrap">
                                        {report.call_status}
                                    </span>
                                </td>
                                <td className="px-3 py-2 text-nowrap">
                                    {report.created_at}
                                </td>
                                <td className="px-3 py-2 text-nowrap">
                                    {report.phone}
                                </td>
                                <td className="px-3 py-2">
                                    {report.contact_name}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={reports.meta.links} queryParams={queryParams} />
        </>
    );
}
