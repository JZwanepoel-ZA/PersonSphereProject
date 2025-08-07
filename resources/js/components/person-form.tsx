import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useEffect, useState } from 'react';
import MultiSelect from 'react-select';

interface Language {
    id: number;
    language_name: string;
}

export interface Person {
    id?: number;
    name: string;
    surname: string;
    south_african_id_number: string;
    mobile_number: string;
    email: string;
    birth_date: string;
    language_id: number;
    language?: Language;
    interests: string[];
}

interface Props {
    person?: Person;
    onSubmit: (data: Omit<Person, 'id' | 'language'>) => Promise<void> | void;
    onCancel: () => void;
}

export default function PersonForm({ person, onSubmit, onCancel }: Props) {
    const [languageOptions, setLanguageOptions] = useState<Language[]>([]);
    const [interestOptions, setInterestOptions] = useState<string[]>([]);

    const [form, setForm] = useState<Omit<Person, 'id' | 'language'>>({
        name: '',
        surname: '',
        south_african_id_number: '',
        mobile_number: '',
        email: '',
        birth_date: '',
        language_id: 0,
        interests: [],
    });

    const [errors, setErrors] = useState<string[]>([]);

    useEffect(() => {
        async function loadOptions() {
            const [langRes, intRes] = await Promise.all([fetch('/api/languages'), fetch('/api/interests')]);

            if (langRes.ok) {
                const langs: Language[] = await langRes.json();
                setLanguageOptions(langs);
            }

            if (intRes.ok) {
                const ints = await intRes.json();
                setInterestOptions(ints);
            }
        }

        loadOptions();
    }, [person]);

    // Sync local form state when editing an existing person
    useEffect(() => {
        setForm({
            name: person?.name ?? '',
            surname: person?.surname ?? '',
            south_african_id_number: person?.south_african_id_number ?? '',
            mobile_number: person?.mobile_number ?? '',
            email: person?.email ?? '',
            birth_date: person?.birth_date?.slice(0, 10) ?? '',
            language_id: person?.language_id ?? person?.language?.id ?? 0,
            interests: person?.interests ?? [],
        });
    }, [person]);

    // Ensure language select reflects the correct value once options load
    useEffect(() => {
        if (languageOptions.length === 0) return;
        setForm((f) => ({
            ...f,
            language_id: person?.language_id ?? person?.language?.id ?? f.language_id ?? languageOptions[0].id,
        }));
    }, [languageOptions, person]);

    function InterestsTagSelect({
        options,
        value,
        onChange,
        id,
    }: {
        options: string[];
        value: string[];
        onChange: (field: keyof Omit<Person, 'id' | 'language'>, value: string[]) => void;
        id: string;
    }) {
        const rsOptions = options.map((o) => ({ label: o, value: o }));
        const rsValue = value.map((v) => ({ label: v, value: v }));

        return (
            <div className="space-y-2">
                <MultiSelect
                    inputId={id}
                    isMulti
                    options={rsOptions}
                    value={rsValue}
                    onChange={(selected) => {
                        const interests = selected ? selected.map((s) => s.value) : [];
                        onChange('interests', interests);
                    }}
                    className="react-select-container"
                    classNamePrefix="react-select"
                    placeholder="Select one or more Interest..."
                />
            </div>
        );
    }

    function handleChange(field: keyof Omit<Person, 'id' | 'language'>, value: string | number | string[]) {
        setForm({ ...form, [field]: value } as Omit<Person, 'id' | 'language'>);
    }

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        const birth = form.birth_date.replace(/-/g, '').slice(2);
        if (form.south_african_id_number.slice(0, 6) !== birth) {
            setErrors(['ID number does not match birth date']);
            return;
        }
        setErrors([]);
        try {
            await onSubmit(form);
        } catch (err) {
            if (err instanceof Error) {
                const messages = (err as Error & { messages?: string[] }).messages;
                setErrors(messages ?? [err.message]);
            } else {
                setErrors([String(err)]);
            }
        }
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-4 rounded-md border p-4">
            {errors.length > 0 && (
                <Alert variant="destructive">
                    <AlertDescription>
                        <ul className="list-disc pl-4">
                            {errors.map((e, i) => (
                                <li key={i}>{e}</li>
                            ))}
                        </ul>
                    </AlertDescription>
                </Alert>
            )}
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="name">
                        Name <span className="text-red-500">*</span>
                    </Label>
                    <Input
                        id="name"
                        required
                        pattern="[A-Za-z\s]+"
                        value={form.name}
                        onChange={(e) => handleChange('name', e.target.value.replace(/[^A-Za-z\s]/g, ''))}
                        placeholder="Name"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="surname">
                        Surname <span className="text-red-500">*</span>
                    </Label>
                    <Input
                        id="surname"
                        required
                        pattern="[A-Za-z\s]+"
                        value={form.surname}
                        onChange={(e) => handleChange('surname', e.target.value.replace(/[^A-Za-z\s]/g, ''))}
                        placeholder="Surname"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="south_african_id_number">
                        South African ID Number <span className="text-red-500">*</span>
                    </Label>
                    <Input
                        id="south_african_id_number"
                        required
                        inputMode="numeric"
                        pattern="\d{13}"
                        maxLength={13}
                        value={form.south_african_id_number}
                        onChange={(e) => handleChange('south_african_id_number', e.target.value.replace(/\D/g, '').slice(0, 13))}
                        placeholder="South African ID Number"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="mobile_number">
                        Mobile Number <span className="text-red-500">*</span>
                    </Label>
                    <Input
                        id="mobile_number"
                        required
                        inputMode="numeric"
                        pattern="\d{10}"
                        maxLength={10}
                        value={form.mobile_number}
                        onChange={(e) => handleChange('mobile_number', e.target.value.replace(/\D/g, '').slice(0, 10))}
                        placeholder="Mobile Number"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="email">
                        Email Address <span className="text-red-500">*</span>
                    </Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        value={form.email}
                        onChange={(e) => handleChange('email', e.target.value)}
                        placeholder="Email Address"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="birth_date">
                        Birth Date <span className="text-red-500">*</span>
                    </Label>
                    <Input
                        id="birth_date"
                        type="date"
                        required
                        value={form.birth_date}
                        onChange={(e) => handleChange('birth_date', e.target.value)}
                        placeholder="Birth Date"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="language_id">
                        Language <span className="text-red-500">*</span>
                    </Label>
                    <Select value={String(form.language_id)} onValueChange={(value) => handleChange('language_id', parseInt(value))}>
                        <SelectTrigger id="language_id">
                            <SelectValue placeholder="Language" />
                        </SelectTrigger>
                        <SelectContent>
                            {languageOptions.map((lang) => (
                                <SelectItem key={lang.id} value={String(lang.id)}>
                                    {lang.language_name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="interests">Interests</Label>
                    <InterestsTagSelect id="interests" options={interestOptions} value={form.interests} onChange={handleChange} />
                </div>
            </div>
            <div className="flex justify-end gap-2">
                <Button type="button" variant="secondary" onClick={onCancel}>
                    Cancel
                </Button>
                <Button type="submit">Save</Button>
            </div>
        </form>
    );
}
