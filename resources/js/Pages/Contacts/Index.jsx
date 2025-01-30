import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

import { Head, Link } from "@inertiajs/react";

import TasksTable from "./TasksTable";

export default function Index({ auth, success, contacts,workflow, queryParams, statuses,error
}) {
    console.log(error)
    return (
        <AuthenticatedLayout
            user={auth.user}
           
        >
            <Head title="Reports" />

            <div className="py-12">
               
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {error&&(<div className="bg-red-700 w-50 text-center text-white">{error}</div>)}

                    <div className="flex justify-between">
                    <Link  href={route('add_steps',workflow.id)} className="text-blue-600 hover:underline text-2xl">
                        Edit Workflow
                    </Link>
                    <div className="text-2xl mr-2">Workflow Name: {workflow.name}</div>
                    <div className={workflow.active?'text-2xl text-green-500':'text-2xl text-red-500'}>Workflow is: {workflow.active?"Active":"Paused"}</div>
                    </div>

                    <div className="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6  dark:text-gray-100">
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
