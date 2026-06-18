

-- create database
create database if not exists tangier_blog;

use tangier_blog;


-- users table
create table if not exists users (
    id_user int auto_increment primary key,
    user_name varchar(100) not null,
    email varchar(150) not null unique,
    password varchar(255) not null,
    role enum('user', 'admin') default 'user',
    created_at timestamp default current_timestamp
);


-- categories table
create table if not exists categories (
    id_category int auto_increment primary key,
    cat_name varchar(100) not null unique,
    created_at timestamp default current_timestamp
);


-- posts table
create table if not exists posts (
    id_post int auto_increment primary key,
    id_category int not null,
    id_user int not null,
    id_approved_by int null,
    title varchar(255) not null,
    image varchar(255),
    content text not null,
    status enum('draft', 'pending', 'published', 'rejected') default 'pending',
    rejection_reason text null,
    approved_at timestamp null,
    created_at timestamp default current_timestamp,
    updated_at timestamp default current_timestamp on update current_timestamp,

    foreign key (id_category) references categories(id_category) on delete cascade,
    foreign key (id_user) references users(id_user) on delete cascade,
    foreign key (id_approved_by) references users(id_user) on delete set null
);


-- contact messages table
create table if not exists contact_messages (
    id_message int auto_increment primary key,
    full_name varchar(150) not null,
    email varchar(150) not null,
    subject varchar(255) not null,
    message text not null,
    created_at timestamp default current_timestamp
);


-- login attempts table (DB-based rate limiting)
create table if not exists login_attempts (
    id_login_attempt int auto_increment primary key,
    email varchar(150) not null unique,
    failed_attempts int not null default 0,
    locked_until datetime null,
    last_attempt timestamp default current_timestamp on update current_timestamp
);


-- comments table
create table if not exists comments (
    id_comment int auto_increment primary key,
    id_post int not null,
    author_name varchar(100) not null,
    comment_text text not null,
    created_at timestamp default current_timestamp,
    foreign key (id_post) references posts(id_post) on delete cascade
);


-- default categories
insert into categories (cat_name) values
    ('Beaches'),
    ('Food & Restaurants'),
    ('Culture & History'),
    ('Nature & Parks'),
    ('Hotels & Riads'),
    ('Nightlife')
on duplicate key update cat_name = values(cat_name);



