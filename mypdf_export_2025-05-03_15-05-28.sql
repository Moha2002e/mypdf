CREATE TABLE pdfs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        file_name TEXT NOT NULL,
        author TEXT,
        tags TEXT,
        category TEXT,
        description TEXT,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
    , course_id INTEGER);

INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (2, 'labo stat synthese', '68162567575d0_1746281831.pdf', 'moi', '', 'laboratoire', '', '2025-05-03 14:17:11', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (3, 'cahier d''exercices', '68162806e6569_1746282502.pdf', 'moi', '', 'exercices', '', '2025-05-03 14:28:22', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (4, 'chapitre 3', '68162836c2c45_1746282550.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:29:10', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (5, 'chapitre 3', '68162858098c1_1746282584.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:29:44', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (6, 'synthese examen thread fiche', '6816288f71e86_1746282639.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:30:39', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (7, 'chapitre 7', '681628ca5ce9e_1746282698.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:31:38', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (8, 'chapitre6', '681628f2f3707_1746282738.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:32:19', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (9, 'astuce stat ', '6816290f4b589_1746282767.pdf', 'moi', '', 'laboratoire', '', '2025-05-03 14:32:47', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (10, 'intro', '6816292bb3271_1746282795.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:33:15', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (11, 'builder', '6816296600058_1746282854.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:34:14', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (12, 'exceptions', '681629801a053_1746282880.pdf', '', '', 'synthese', '', '2025-05-03 14:34:40', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (13, 'threads', '6816299af01ba_1746282906.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:35:07', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (14, 'chapitre2', '681629b4881cb_1746282932.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:35:32', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (15, 'synthese complete ', '681629f250cfc_1746282994.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:36:34', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (16, 'chapitre 4', '68162a1c15362_1746283036.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:37:16', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (17, 'chapitre 5 ', '68162a50ca6f0_1746283088.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:38:08', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (18, 'fiche threads ', '68162a7f67b9a_1746283135.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:38:55', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (19, 'adapter', '68162a9bd8174_1746283163.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:39:23', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (20, 'composite', '68162abaa94cf_1746283194.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:39:54', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (21, 'factory', '68162b006e406_1746283264.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:41:04', NULL);
INSERT INTO pdfs (id, title, file_name, author, tags, category, description, uploaded_at, course_id) VALUES (22, 'synthese examen ', '68162b2422996_1746283300.pdf', 'moi', '', 'synthese', '', '2025-05-03 14:41:40', NULL);

CREATE TABLE settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_name TEXT NOT NULL UNIQUE,
        setting_value TEXT NOT NULL
    );

INSERT INTO settings (id, setting_name, setting_value) VALUES (3, 'items_per_page', 12);
INSERT INTO settings (id, setting_name, setting_value) VALUES (4187, 'view_mode', 'grid');
INSERT INTO settings (id, setting_name, setting_value) VALUES (4693, 'theme', 'light');

CREATE TABLE courses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

INSERT INTO courses (id, name, description, created_at) VALUES (1, 'Analyse 1', 'Cours d''analyse mathématique niveau 1', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (2, 'Analyse 2', 'Cours d''analyse mathématique niveau 2', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (3, 'SGBD 3', 'Systèmes de gestion de bases de données niveau 3', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (4, 'SGBD 4', 'Systèmes de gestion de bases de données niveau 4', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (5, 'BD Avancées', 'Bases de données avancées', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (6, 'JAVA', 'Programmation en Java', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (7, 'C Thread', 'Programmation C avec threads', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (8, 'C Linux', 'Programmation C sous Linux', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (9, 'CPP', 'Programmation C++', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (10, 'C#', 'Programmation C#', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (11, 'Design Pattern', 'Patrons de conception logicielle', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (12, 'Complete BD', 'Bases de données complètes', '2025-05-03 14:14:54');
INSERT INTO courses (id, name, description, created_at) VALUES (13, 'Statistique', 'Analyse statistique', '2025-05-03 14:14:54');

