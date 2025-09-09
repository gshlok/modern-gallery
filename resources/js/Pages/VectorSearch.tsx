import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import VectorSearch from '@/Components/VectorSearch';

interface PageProps {
    user: any;
}

const VectorSearchPage: React.FC<PageProps> = ({ user }) => {
    return (
        <AppLayout>
            <Head title="Vector Search" />
            
            <div className="min-h-screen bg-gray-50">
                <VectorSearch user={user} />
            </div>
        </AppLayout>
    );
};

export default VectorSearchPage;