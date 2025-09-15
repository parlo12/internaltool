import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react'; // add useEffect
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
            <div className="w-full min-h-screen bg-white">
                <div className="w-full py-12 px-0">
                    <div className="w-full px-4">
                        <div className="overflow-hidden bg-white shadow-sm rounded-lg border border-gray-200">
                            <div className="flex items-center justify-between text-black py-4 px-4">
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
