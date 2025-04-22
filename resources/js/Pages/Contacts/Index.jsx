import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

import { Head, Link } from "@inertiajs/react";

import TasksTable from "./TasksTable";

export default function Index({ auth, success, contacts, workflow, queryParams, statuses, error }) {
    console.log(error);
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Reports" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px- lg:px-2">
                    {error && (
                        <div className="bg-red-700 w-full text-center text-white py-2 rounded mb-4">
                            {error}
                        </div>
                    )}

                    <div className="flex flex-col sm:flex-row justify-between items-center mb-4">
                        <Link
                            href={route('add_steps', workflow.id)}
                            className="text-blue-600 hover:underline text-lg sm:text-2xl mb-2 sm:mb-0"
                        >
                            Edit Workflow
                        </Link>
                        <div className="text-lg sm:text-2xl text-center sm:text-right">
                            Workflow Name: {workflow.name}
                        </div>
                        <div
                            className={`text-lg sm:text-2xl ${
                                workflow.active ? 'text-green-500' : 'text-red-500'
                            }`}
                        >
                            Workflow is: {workflow.active ? "Active" : "Paused"}
                        </div>
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-2 sm:p-2 dark:text-gray-100">
                            <TasksTable
                                contacts={contacts}
                                queryParams={queryParams}
                                success={success}
                                workflow_id={workflow.id}
                                statuses={statuses}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
