import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import AiGeneration from '@/Components/AiGeneration';

interface PageProps {
    user: any;
    providers: string[];
}

const AiGenerationPage: React.FC<PageProps> = ({ user, providers }) => {
    return (
        <AppLayout>
            <Head title=\"AI Image Generation\" />
            
            <div className=\"min-h-screen bg-gray-50\">
                <AiGeneration user={user} providers={providers} />
            </div>
        </AppLayout>
    );
};

export default AiGenerationPage;