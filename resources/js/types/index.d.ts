export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    nik: string;
    specimen_path: string | null;
}

export interface DepartmentRow {
    id: number;
    name: string;
    code: string;
    is_active: boolean | number;
    parent_id: number | null;
    parent?: { id: number; name: string } | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
