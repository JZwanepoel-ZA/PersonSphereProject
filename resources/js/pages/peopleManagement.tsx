import PersonForm, { Person } from '@/components/person-form';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'People Management',
        href: '/peopleManagement',
    },
];

function maskIdNumber(id: string) {
    return id.slice(0, 6) + '*'.repeat(Math.max(0, id.length - 10)) + id.slice(-4);
}

export default function PeopleManagement() {
    const [people, setPeople] = useState<Person[]>([]);
    const [showForm, setShowForm] = useState(false);
    const [editing, setEditing] = useState<Person | undefined>();
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
    const [visibleIds, setVisibleIds] = useState<Set<number>>(new Set());
    const [search, setSearch] = useState('');

    type PersonApi = Omit<Person, 'interests'> & {
        interests?: string[];
        interests_relation?: { interest_name: string }[];
    };

    const loadPeople = useCallback(async () => {
        const res = await fetch('/api/people', { headers: { Accept: 'application/json' } });
        if (res.ok) {
            const data: PersonApi[] = await res.json();
            setPeople(
                data.map((p) => ({
                    ...p,
                    birth_date: p.birth_date?.slice(0, 10) ?? '',
                    interests: p.interests ?? p.interests_relation?.map((i) => i.interest_name) ?? [],
                    language: p.language,
                    language_id: p.language_id ?? p.language?.id ?? 0,
                })),
            );
        }
    }, []);

    useEffect(() => {
        loadPeople();
    }, [loadPeople]);

    const filteredPeople = people.filter((p) => {
        const query = search.toLowerCase();
        return (
            p.name?.toLowerCase().includes(query) ||
            p.surname?.toLowerCase().includes(query) ||
            p.email?.toLowerCase().includes(query) ||
            p.mobile_number?.toLowerCase().includes(query) ||
            p.south_african_id_number?.toLowerCase().includes(query) ||
            p.language?.language_name?.toLowerCase().includes(query) ||
            p.interests.join(',').toLowerCase().includes(query)
        );
    });

    async function handleSubmit(data: Omit<Person, 'id' | 'language'>) {
        const method = editing ? 'PUT' : 'POST';
        const url = editing ? `/api/people/${editing.id}` : '/api/people';
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify(data),
        });

        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            const errors = Object.values(data.errors ?? {}).flat();
            const message = errors.join(' ') || data.message || 'Failed to save person';
            const error = new Error(message);
            // preserve individual validation messages for the form to render
            (error as Error & { messages?: string[] }).messages = errors.length ? errors : [message];
            throw error;
        }

        setMessage({ type: 'success', text: editing ? 'Person updated' : 'Person created' });
        setShowForm(false);
        setEditing(undefined);
        await loadPeople();
    }

    async function handleDelete(id: number) {
        if (!confirm('Are you sure you want to delete this person?')) return;
        const res = await fetch(`/api/people/${id}`, {
            method: 'DELETE',
            headers: { Accept: 'application/json' },
        });
        if (res.ok) {
            setMessage({ type: 'success', text: 'Person deleted' });
            await loadPeople();
        } else {
            const data = await res.json().catch(() => ({}));
            const text = data.message || 'Failed to delete person';
            setMessage({ type: 'error', text });
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {message && (
                    <Alert variant={message.type === 'success' ? 'default' : 'destructive'}>
                        <AlertTitle>{message.type === 'success' ? 'Success' : 'Error'}</AlertTitle>
                        <AlertDescription>{message.text}</AlertDescription>
                    </Alert>
                )}
                <div className="flex items-center justify-between">
                    <Input
                        placeholder="Search..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="max-w-xs"
                    />
                    <Button
                        onClick={() => {
                            setEditing(undefined);
                            setShowForm(true);
                        }}
                    >
                        Add Person
                    </Button>
                </div>
                {showForm && (
                    <PersonForm
                        person={editing}
                        onSubmit={handleSubmit}
                        onCancel={() => {
                            setShowForm(false);
                            setEditing(undefined);
                        }}
                    />
                )}
                <div className="relative flex-1 overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    {filteredPeople.length === 0 ? (
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    ) : (
                        <table className="w-full border-collapse text-left">
                            <thead>
                                <tr>
                                    <th className="border px-2 py-1">Name</th>
                                    <th className="border px-2 py-1">Surname</th>
                                    <th className="border px-2 py-1">Email</th>
                                    <th className="border px-2 py-1">Mobile</th>
                                    <th className="border px-2 py-1">ID Number</th>
                                    <th className="border px-2 py-1">Language</th>
                                    <th className="border px-2 py-1">Interests</th>
                                    <th className="border px-2 py-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredPeople.map((person) => (
                                    <tr key={person.id}>
                                        <td className="border px-2 py-1">{person.name}</td>
                                        <td className="border px-2 py-1">{person.surname}</td>
                                        <td className="border px-2 py-1">{person.email}</td>
                                        <td className="border px-2 py-1">{person.mobile_number}</td>
                                        <td className="border px-2 py-1">
                                            <div className="flex items-center gap-2">
                                                <span>
                                                    {visibleIds.has(person.id!)
                                                        ? person.south_african_id_number
                                                        : maskIdNumber(person.south_african_id_number)}
                                                </span>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>
                                                        setVisibleIds((prev) => {
                                                            const next = new Set(prev);
                                                            if (person.id != null) {
                                                                if (next.has(person.id)) {
                                                                    next.delete(person.id);
                                                                } else {
                                                                    next.add(person.id);
                                                                }
                                                            }
                                                            return next;
                                                        })
                                                    }
                                                >
                                                    {visibleIds.has(person.id!) ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                </Button>
                                            </div>
                                        </td>
                                        <td className="border px-2 py-1">{person.language?.language_name}</td>
                                        <td className="border px-2 py-1">{person.interests.join(', ')}</td>
                                        <td className="flex gap-2 border px-2 py-1">
                                            <Button
                                                variant="secondary"
                                                size="sm"
                                                onClick={() => {
                                                    setEditing(person);
                                                    setShowForm(true);
                                                }}
                                            >
                                                Edit
                                            </Button>
                                            <Button variant="destructive" size="sm" onClick={() => handleDelete(person.id!)}>
                                                Delete
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
