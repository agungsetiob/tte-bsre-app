export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    nik: string;
    specimen_path: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
