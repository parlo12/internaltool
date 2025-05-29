import React from "react";
import { Link, usePage } from "@inertiajs/react";

export default function Sidebar() {
    const { url } = usePage();
    return (
        <aside className="w-64 bg-white shadow-lg min-h-screen flex flex-col">
            <div className="p-6 text-2xl font-bold text-indigo-700 border-b">
                Workflow Manager
            </div>
            <nav className="flex-1 p-4 space-y-2">
                <Link href={route("workflows.index")} className="block px-4 py-2 rounded hover:bg-indigo-100 text-gray-700 font-medium">
                    View Workflows
                </Link>
                <Link href={route("workflows.create")} className="block px-4 py-2 rounded hover:bg-indigo-100 text-gray-700 font-medium">
                    Create Workflow
                </Link>
                <Link href={route("folders.index")} className="block px-4 py-2 rounded hover:bg-indigo-100 text-gray-700 font-medium">
                    View Folders
                </Link>
                <Link href={route("folders.create")} className="block px-4 py-2 rounded hover:bg-indigo-100 text-gray-700 font-medium">
                    Create Folder
                </Link>
            </nav>
        </aside>
    );
}
