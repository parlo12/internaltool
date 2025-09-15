import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import Sidebar from '@/Components/Sidebar';
import { useState, useEffect } from 'react'; // add useEffect
import ContactsTable from './ContactsTable';
import ContactsImportFailuresTable from './ContactsImportFailuresTable';

export default function ContactImportFailures({ auth, failures, group_id, groupName, success, error, queryParams = {} }) {
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);
    const [activeView, setActiveView] = useState('dashboard');
    const [selectedGroup, setSelectedGroup] = useState(null);
    console.log(failures)
    // When component loads, try to load activeView from localStorage
    useEffect(() => {
        const savedView = localStorage.getItem('activeView');
        if (savedView) {
            setActiveView(savedView);
        }
    }, []);
    // Handler to clear all import failures for the group
    const handleClearFailures = () => {
        if (confirm('Are you sure you want to clear all import failures for this group?')) {
            router.delete(route('contacts.importFailures.clear', group_id), {
                onSuccess: () => { },
            });
        }
    };

    const handleShowContactGroups = () => {
        setActiveView('contactGroups');
        setSelectedGroup(null);
        localStorage.setItem('activeView', 'contactGroups'); // save to localStorage
    };

    const handleSelectGroup = (group) => {
        console.log(group)
        setSelectedGroup(group);
        setActiveView('contacts');
        localStorage.setItem('activeView', 'contacts'); // save to localStorage
    };

    const handleBackToGroups = () => {
        setActiveView('contactGroups');
        setSelectedGroup(null);
        localStorage.setItem('activeView', 'contactGroups'); // save to localStorage
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />
            <div className="flex flex-col md:flex-row">
                {/* Main Content */}
                <div className="w-full md:w-3/4 py-12">
                    <div className="mx-auto max-w-7xl sm:px-2 lg:px-4">
                        <div className="overflow-hidden bg-dark-gray shadow-sm sm:rounded-lg border border-onyx">
                            <div className="flex items-center justify-between text-[#FAFAFA] py-4">
                                <span>Contact Import Failures</span>
                                <button
                                    onClick={handleClearFailures}
                                    className="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded shadow transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-pink-400"
                                >
                                    Clear All Failures
                                </button>
                            </div>
                            {failures.data ? (
                                <ContactsImportFailuresTable
                                    failures={failures}
                                    queryParams={queryParams}
                                    success={success}
                                    error={error}
                                    group_id={group_id}
                                />) : (
                                <div className="bg-emerald-500 py-2 px-4 text-white rounded mb-4 shadow-md">
                                    No Failures Found
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
