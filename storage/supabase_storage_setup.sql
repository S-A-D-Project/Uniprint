-- Supabase Storage Setup SQL
-- Run these commands in your Supabase SQL Editor

-- Create the main Uniprint bucket (single bucket with folder structure)
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values 
    ('Uniprint', 'Uniprint', true, 52428800, array['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg', 'application/pdf', 'application/postscript', 'image/vnd.adobe.photoshop'])
on conflict (id) do update set
    public = true,
    file_size_limit = 52428800,
    allowed_mime_types = array['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg', 'application/pdf', 'application/postscript', 'image/vnd.adobe.photoshop'];

-- Drop old policies if they exist (for separate buckets)
drop policy if exists "Public Access" on storage.objects;
drop policy if exists "Allow Insert" on storage.objects;
drop policy if exists "Allow Update" on storage.objects;
drop policy if exists "Allow Delete" on storage.objects;
drop policy if exists "Public Access Logos" on storage.objects;
drop policy if exists "Allow Insert Logos" on storage.objects;
drop policy if exists "Allow Update Logos" on storage.objects;
drop policy if exists "Allow Delete Logos" on storage.objects;
drop policy if exists "Public Access Service Images" on storage.objects;
drop policy if exists "Allow Insert Service Images" on storage.objects;
drop policy if exists "Allow Update Service Images" on storage.objects;
drop policy if exists "Allow Delete Service Images" on storage.objects;

-- Enable public read access for all objects in Uniprint bucket
create policy "Public Read Access"
on storage.objects for select
using (bucket_id = 'Uniprint');

-- Enable insert for all authenticated users (using API key)
create policy "Allow Insert"
on storage.objects for insert
with check (bucket_id = 'Uniprint');

-- Enable update for all authenticated users
create policy "Allow Update"
on storage.objects for update
using (bucket_id = 'Uniprint');

-- Enable delete for all authenticated users
create policy "Allow Delete"
on storage.objects for delete
using (bucket_id = 'Uniprint');
