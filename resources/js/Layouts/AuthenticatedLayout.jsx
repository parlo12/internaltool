import { useState } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link } from '@inertiajs/react';

export default function Authenticated({ user, header, children }) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="bg-white border-b border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="shrink-0 flex items-center">
                                <Link href="/">
                                    <ApplicationLogo className="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            {/* Desktop Navigation */}
                            <div className="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink href={route('create-workflow')} active={route().current('create-workflow')}>
                                    Workflow
                                </NavLink>
                                <NavLink href={route('workflow-reports.index')} active={route().current('workflow-reports.index')}>
                                    Workflow Reports
                                </NavLink>
                                <NavLink href={route('ai.index')} active={route().current('ai.index')}>
                                    Assistants
                                </NavLink>
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md  space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                            <button
                                                type="button"
                                                className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                Upload CSV
                                                <svg
                                                    className="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('upload.csv')} active={route().current('upload.csv')}>
                                            With Processing
                                        </Dropdown.Link>
                                        <Dropdown.Link href={route('contact-import-failures.index')} active={route().current('contact-import-failures.index')}>
                                            Without Processing
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>

                                {/* Pipelines Dropdown - Desktop */}
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center px-3 py-6 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                Pipelines
                                                <svg
                                                    className="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('follow-ups.index')}>Follow Ups</Dropdown.Link>
                                        <Dropdown.Link href={route('wrong-numbers.index')}>Wrong Numbers</Dropdown.Link>
                                        <Dropdown.Link href={route('under-contracts.index')}>Under Contracts</Dropdown.Link>
                                        <Dropdown.Link href={route('fresh-leads.index')}>Fresh Leads</Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>

                                <NavLink href={route('admin.index')} active={route().current('admin.index')}>
                                    Admin
                                </NavLink>
                            </div>
                        </div>

                        {/* Profile Dropdown */}
                        <div className="hidden sm:flex sm:items-center sm:ms-6">
                            <div className="ms-3 relative">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {user.name}
                                                <svg
                                                    className="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button">
                                            Log Out
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        {/* Mobile Menu Button */}
                        <div className="-me-2 flex items-center sm:hidden">
                            <button
                                onClick={() => setShowingNavigationDropdown(!showingNavigationDropdown)}
                                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {/* Mobile Navigation Menu */}
                {showingNavigationDropdown && (
                    <div className="sm:hidden">
                        <div className="pt-2 pb-3 space-y-1">
                            <ResponsiveNavLink href={route('create-workflow')} active={route().current('create-workflow')}>
                                Workflow
                            </ResponsiveNavLink>
                            <ResponsiveNavLink href={route('workflow-reports.index')} active={route().current('workflow-reports.index')}>
                                Workflow Reports
                            </ResponsiveNavLink>
                            <ResponsiveNavLink href={route('ai.index')} active={route().current('ai.index')}>
                                Assistants
                            </ResponsiveNavLink>
                            <ResponsiveNavLink href={route('upload.csv')} active={route().current('upload.csv')}>
                                Process CSV
                            </ResponsiveNavLink>

                            {/* Pipelines Dropdown - Mobile */}
                            <div className="px-4 py-2 font-medium text-gray-500">Pipelines</div>
                            <ResponsiveNavLink href={route('follow-ups.index')}>Follow Ups</ResponsiveNavLink>
                            <ResponsiveNavLink href={route('wrong-numbers.index')}>Wrong Numbers</ResponsiveNavLink>
                            <ResponsiveNavLink href={route('under-contracts.index')}>Under Contracts</ResponsiveNavLink>

                            <ResponsiveNavLink href={route('admin.index')} active={route().current('admin.index')}>
                                Admin
                            </ResponsiveNavLink>
                        </div>
                    </div>
                )}
            </nav>

            {header && <header className="bg-white shadow"><div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{header}</div></header>}

            <main>{children}</main>
        </div>
    );
}
