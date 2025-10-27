USE internship_management_dev;

-- ============================================
-- SPs: users
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_user(
    IN p_full_name VARCHAR(100),
    IN p_institutional_email VARCHAR(255),
    IN p_role ENUM('INTERN', 'SUPERVISOR', 'ADMIN'),
    IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO users (full_name, institutional_email, role, password)
    VALUES (p_full_name, p_institutional_email, p_role, p_password);
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_user(IN p_id INT)
BEGIN
    SELECT * FROM users WHERE id = p_id;
END $$

-- GET BY EMAIL
CREATE PROCEDURE sp_get_by_email_user(IN p_email VARCHAR(255))
BEGIN
	SELECT * FROM users WHERE institutional_email = p_email;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_users()
BEGIN
    SELECT * FROM users;
END $$

-- GET ALL BY ROLE
CREATE PROCEDURE sp_get_by_role_users(IN p_role ENUM('INTERN', 'SUPERVISOR', 'ADMIN'))
BEGIN
	SELECT * FROM users WHERE role = p_role;
END $$

-- UPDATE
CREATE PROCEDURE sp_update_user(
    IN p_id INT,
    IN p_full_name VARCHAR(100),
    IN p_institutional_email VARCHAR(255),
    IN p_role ENUM('INTERN', 'SUPERVISOR', 'ADMIN'),
    IN p_password VARCHAR(255),
    IN p_active TINYINT(1)
)
BEGIN
    UPDATE users
    SET full_name = p_full_name,
        institutional_email = p_institutional_email,
        role = p_role,
        password = p_password,
        active = p_active
    WHERE id = p_id;
END $$

-- DELETE (lógico)
CREATE PROCEDURE sp_delete_user(IN p_id INT)
BEGIN
    UPDATE users
    SET active = 0
    WHERE id = p_id;
END $$

DELIMITER ;


-- ============================================
-- SPs: supervisors
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_supervisor(
    IN p_user_id INT,
    IN p_area VARCHAR(255)
)
BEGIN
    INSERT INTO supervisors (user_id, area)
    VALUES (p_user_id, p_area);
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_supervisor(IN p_id INT)
BEGIN
    SELECT * FROM supervisors WHERE id = p_id;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_supervisors()
BEGIN
    SELECT * FROM supervisors;
END $$

-- UPDATE
CREATE PROCEDURE sp_update_supervisor(
    IN p_id INT,
    IN p_user_id INT,
    IN p_area VARCHAR(255)
)
BEGIN
    UPDATE supervisors
    SET user_id = p_user_id,
        area = p_area
    WHERE id = p_id;
END $$

-- DELETE (físico)
CREATE PROCEDURE sp_delete_supervisor(IN p_id INT)
BEGIN
    DELETE FROM supervisors WHERE id = p_id;
END $$

DELIMITER ;


-- ============================================
-- SPs: interns
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_intern(
    IN p_user_id INT,
    IN p_university VARCHAR(128),
    IN p_career VARCHAR(128),
    IN p_internship_start_date DATE,
    IN p_internship_end_date DATE,
    IN p_supervisor_id INT
)
BEGIN
    INSERT INTO interns (
        user_id, university, career, internship_start_date,
        internship_end_date, supervisor_id
    ) VALUES (
        p_user_id, p_university, p_career,
        p_internship_start_date, p_internship_end_date, p_supervisor_id
    );
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_intern(IN p_id INT)
BEGIN
    SELECT * FROM interns WHERE id = p_id;
END $$

-- GET BY USER ID
CREATE PROCEDURE sp_get_by_user_id_intern(IN p_user_id INT)
BEGIN
	SELECT * FROM interns WHERE user_id = p_user_id;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_interns()
BEGIN
    SELECT * FROM interns;
END $$

-- GET ALL BY SUPERVISOR ID
CREATE PROCEDURE sp_get_all_by_supervisor_id_interns(IN p_supervisor_id INT)
BEGIN
	SELECT * FROM interns WHERE supervisor_id = p_supervisor_id;
END $$

-- GET ALL ACTIVE
CREATE PROCEDURE sp_get_all_active_interns()
BEGIN
	SELECT * FROM interns WHERE active = 1;
END $$

-- UPDATE
CREATE PROCEDURE sp_update_intern(
    IN p_id INT,
    IN p_user_id INT,
    IN p_university VARCHAR(128),
    IN p_career VARCHAR(128),
    IN p_internship_start_date DATE,
    IN p_internship_end_date DATE,
    IN p_supervisor_id INT,
    IN p_active TINYINT(1)
)
BEGIN
    UPDATE interns
    SET user_id = p_user_id,
        university = p_university,
        career = p_career,
        internship_start_date = p_internship_start_date,
        internship_end_date = p_internship_end_date,
        supervisor_id = p_supervisor_id,
        active = p_active
    WHERE id = p_id;
END $$

-- DELETE (lógico)
CREATE PROCEDURE sp_delete_intern(IN p_id INT)
BEGIN
    UPDATE interns
    SET active = 0
    WHERE id = p_id;
END $$

DELIMITER ;


-- ============================================
-- SPs: meetings
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_meeting(
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_start_datetime TIMESTAMP,
    IN p_estimated_duration INT,
    IN p_type ENUM('presential', 'virtual'),
    IN p_organizer_id INT
)
BEGIN
    INSERT INTO meetings (
        title, description, start_datetime,
        estimated_duration, type, organizer_id
    ) VALUES (
        p_title, p_description, p_start_datetime,
        p_estimated_duration, p_type, p_organizer_id
    );
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_meeting(IN p_id INT)
BEGIN
    SELECT * FROM meetings WHERE id = p_id;
END $$

-- GET BY ORGANIZER ID
CREATE PROCEDURE sp_get_by_organizer_id_meeting(IN p_organizer_id INT)
BEGIN
	SELECT * FROM meetings WHERE organizer_id = p_organizer_id;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_meetings()
BEGIN
    SELECT * FROM meetings;
END $$

-- GET ALL FOR USER
CREATE PROCEDURE sp_get_all_for_user(IN p_user_id INT)
BEGIN
	SELECT
		m.id AS meeting_id,
        m.title,
        m.description,
        m.start_datetime,
        m.estimated_duration,
        m.type,
        m.organizer_id,
        m.created_at,
        ma.attended,
        ma.comments
	FROM meetings m
    INNER JOIN meeting_attendees ma ON ma.meeting_id = m.id
    WHERE ma.user_id = p_user_id;
END $$

-- UPDATE
CREATE PROCEDURE sp_update_meeting(
    IN p_id INT,
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_start_datetime TIMESTAMP,
    IN p_estimated_duration INT,
    IN p_type ENUM('presential', 'virtual'),
    IN p_organizer_id INT
)
BEGIN
    UPDATE meetings
    SET title = p_title,
        description = p_description,
        start_datetime = p_start_datetime,
        estimated_duration = p_estimated_duration,
        type = p_type,
        organizer_id = p_organizer_id
    WHERE id = p_id;
END $$

-- DELETE (físico)
CREATE PROCEDURE sp_delete_meeting(IN p_id INT)
BEGIN
    DELETE FROM meetings WHERE id = p_id;
END $$

DELIMITER ;


-- ============================================
-- SPs: meeting_attendees
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_meeting_attendee(
    IN p_meeting_id INT,
    IN p_user_id INT,
    IN p_attended TINYINT(1),
    IN p_comments TEXT
)
BEGIN
    INSERT INTO meeting_attendees (
        meeting_id, user_id, attended, comments
    ) VALUES (
        p_meeting_id, p_user_id, p_attended, p_comments
    );
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_meeting_attendee(IN p_id INT)
BEGIN
    SELECT * FROM meeting_attendees WHERE id = p_id;
END $$

-- GET BY MEETING ID & USER ID
CREATE PROCEDURE sp_get_by_meeting_id_and_user_id_meeting_attendee(
	IN p_meeting_id INT,
    IN p_user_id INT
)
BEGIN
	SELECT * FROM meeting_attendees
    WHERE
		meeting_id = p_meeting_id AND
        user_id = p_user_id;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_meeting_attendees()
BEGIN
    SELECT * FROM meeting_attendees;
END $$

-- GET ALL BY MEETING ID
CREATE PROCEDURE sp_get_all_by_meeting_id_meeting_attendees(IN p_meeting_id INT)
BEGIN
	SELECT * FROM meeting_attendees WHERE meeting_id = p_meeting_id;
END $$

-- UPDATE
CREATE PROCEDURE sp_update_meeting_attendee(
    IN p_id INT,
    IN p_meeting_id INT,
    IN p_user_id INT,
    IN p_attended TINYINT(1),
    IN p_comments TEXT
)
BEGIN
    UPDATE meeting_attendees
    SET meeting_id = p_meeting_id,
        user_id = p_user_id,
        attended = p_attended,
        comments = p_comments
    WHERE id = p_id;
END $$

-- DELETE (físico)
CREATE PROCEDURE sp_delete_meeting_attendee(IN p_id INT)
BEGIN
    DELETE FROM meeting_attendees WHERE id = p_id;
END $$

DELIMITER ;


-- ============================================
-- SPs: messages
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_message(
    IN p_title VARCHAR(255),
    IN p_content TEXT,
    IN p_remitent_id INT,
    IN p_recipient_type ENUM('intern', 'supervisor', 'all')
)
BEGIN
    INSERT INTO messages (
        title, content, remitent_id, recipient_type
    ) VALUES (
        p_title, p_content, p_remitent_id, p_recipient_type
    );
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_message(IN p_id INT)
BEGIN
    SELECT * FROM messages WHERE id = p_id;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_messages()
BEGIN
    SELECT * FROM messages;
END $$

-- GET ALL BY REMITENT ID
CREATE PROCEDURE sp_get_by_remitent_id_messages(IN p_remitent_id INT)
BEGIN
	SELECT * FROM messages WHERE remitent_id = p_remitent_id;
END $$

-- GET ALL BY USER ID
CREATE PROCEDURE sp_get_inbox_by_user_id_messages(IN p_user_id INT)
BEGIN
	SELECT * FROM messages m
    INNER JOIN message_recipients mr ON mr.message_id = m.id
    WHERE mr.user_id = p_user_id;
END $$

-- GET ALL UNREAD MESSAGES BY USER ID
CREATE PROCEDURE sp_get_unread_messages_by_user_id_messages(IN p_user_id INT)
BEGIN
	SELECT * FROM messages m
    INNER JOIN message_recipients mr ON mr.message_id = m.id
    WHERE mr.user_id = p_user_id AND mr.readed = 0;
END $$

-- UPDATE
CREATE PROCEDURE sp_update_message(
    IN p_id INT,
    IN p_title VARCHAR(255),
    IN p_content TEXT,
    IN p_remitent_id INT,
    IN p_recipient_type ENUM('intern', 'supervisor', 'all'),
    IN p_active TINYINT(1)
)
BEGIN
    UPDATE messages
    SET title = p_title,
        content = p_content,
        remitent_id = p_remitent_id,
        recipient_type = p_recipient_type,
        active = p_active
    WHERE id = p_id;
END $$

-- DELETE (lógico)
CREATE PROCEDURE sp_delete_message(IN p_id INT)
BEGIN
    UPDATE messages
    SET active = 0
    WHERE id = p_id;
END $$

DELIMITER ;


-- ============================================
-- SPs: message_recipients
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_message_recipient(
    IN p_message_id INT,
    IN p_user_id INT,
    IN p_readed TINYINT(1),
    IN p_read_date DATETIME
)
BEGIN
    INSERT INTO message_recipients (
        message_id, user_id, readed, read_date
    ) VALUES (
        p_message_id, p_user_id, p_readed, p_read_date
    );
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_message_recipient(IN p_id INT)
BEGIN
    SELECT * FROM message_recipients WHERE id = p_id;
END $$

-- GET BY MESSAGE ID & USER ID
CREATE PROCEDURE sp_get_by_message_id_and_user_id_message_recipient(
	IN p_message_id INT,
    IN p_user_id INT
)
BEGIN
	SELECT * FROM message_recipients
    WHERE
		message_id = p_message_id AND
        user_id = p_user_id;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_message_recipients()
BEGIN
    SELECT * FROM message_recipients;
END $$

-- UPDATE
CREATE PROCEDURE sp_update_message_recipient(
    IN p_id INT,
    IN p_message_id INT,
    IN p_user_id INT,
    IN p_readed TINYINT(1),
    IN p_read_date DATETIME
)
BEGIN
    UPDATE message_recipients
    SET message_id = p_message_id,
        user_id = p_user_id,
        readed = p_readed,
        read_date = p_read_date
    WHERE id = p_id;
END $$

-- MARK MESSAGE AS READ
CREATE PROCEDURE sp_mark_message_as_read_message_recipeint(
	IN p_message_id INT,
    IN p_user_id INT
)
BEGIN
	UPDATE message_recipients
    SET
		readed = 1,
        read_date = NOW()
    WHERE
		message_id = p_message_id AND
        user_id = p_user_id;
END $$

-- DELETE (físico)
CREATE PROCEDURE sp_delete_message_recipient(IN p_id INT)
BEGIN
    DELETE FROM message_recipients WHERE id = p_id;
END $$

DELIMITER ;


-- ============================================
-- SPs: activity_reports
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_activity_report(
    IN p_intern_id INT,
    IN p_supervisor_id INT,
    IN p_title VARCHAR(255),
    IN p_content TEXT,
    IN p_send_date TIMESTAMP,
    IN p_revision_date TIMESTAMP,
    IN p_revision_state ENUM('Pending','Reviewed','revision_required'),
    IN p_supervisor_comment TEXT
)
BEGIN
    INSERT INTO activity_reports (
        intern_id, supervisor_id, title, content,
        send_date, revision_date, revision_state, supervisor_comment
    ) VALUES (
        p_intern_id, p_supervisor_id, p_title, p_content,
        p_send_date, p_revision_date, p_revision_state, p_supervisor_comment
    );
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_activity_report(IN p_id INT)
BEGIN
    SELECT * FROM activity_reports WHERE id = p_id;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_activity_reports()
BEGIN
    SELECT * FROM activity_reports;
END $$

-- GET ALL BY INTERN ID
CREATE PROCEDURE sp_get_by_intern_id_activity_reports(IN p_intern_id INT)
BEGIN
	SELECT * FROM activity_reports WHERE intern_id = p_intern_id;
END $$

-- GET ALL BY SUPERVISOR ID
CREATE PROCEDURE sp_get_by_supervisor_id_activity_reports(IN p_supervisor_id INT)
BEGIN
	SELECT * FROM activity_reports WHERE supervisor_id = p_supervisor_id;
END $$

-- GET ALL PENDING REPORTS BY SUPERVISOR ID
CREATE PROCEDURE sp_get_pending_reports_by_supervisor_id_activity_reports(IN p_supervisor_id INT)
BEGIN
	SELECT * FROM activity_reports WHERE supervisor_id = p_supervisor_id AND revision_state = 'Pending';
END $$

-- GET ALL PENDING REPORTS
CREATE PROCEDURE sp_get_all_pending_reports_activity_reports()
BEGIN
	SELECT * FROM activity_reports WHERE revision_state = 'Pending';
END $$

-- UPDATE
CREATE PROCEDURE sp_update_activity_report(
    IN p_id INT,
    IN p_intern_id INT,
    IN p_supervisor_id INT,
    IN p_title VARCHAR(255),
    IN p_content TEXT,
    IN p_send_date TIMESTAMP,
    IN p_revision_date TIMESTAMP,
    IN p_revision_state ENUM('Pending','Reviewed','revision_required'),
    IN p_supervisor_comment TEXT,
    IN p_active TINYINT(1)
)
BEGIN
    UPDATE activity_reports
    SET intern_id = p_intern_id,
        supervisor_id = p_supervisor_id,
        title = p_title,
        content = p_content,
        send_date = p_send_date,
        revision_date = p_revision_date,
        revision_state = p_revision_state,
        supervisor_comment = p_supervisor_comment,
        active = p_active
    WHERE id = p_id;
END $$

-- DELETE (lógico)
CREATE PROCEDURE sp_delete_activity_report(IN p_id INT)
BEGIN
    UPDATE activity_reports
    SET active = 0
    WHERE id = p_id;
END $$

DELIMITER ;


-- ============================================
-- SPs: documents
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_document(
    IN p_original_name VARCHAR(255),
    IN p_path VARCHAR(500),
    IN p_document_type ENUM('CV','certificate','report','other'),
    IN p_description TEXT,
    IN p_up_by_id INT
)
BEGIN
    INSERT INTO documents (
        original_name, path, document_type, description, up_by_id
    ) VALUES (
        p_original_name, p_path, p_document_type, p_description, p_up_by_id
    );
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_document(IN p_id INT)
BEGIN
    SELECT * FROM documents WHERE id = p_id;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_documents()
BEGIN
    SELECT * FROM documents;
END $$

-- GET ALL BY INTERN ID
CREATE PROCEDURE sp_get_all_by_intern_id_documents(IN p_intern_id INT)
BEGIN
	SELECT * FROM documents d
    INNER JOIN intern_documents id ON id.document_id = d.id
    WHERE id.intern_id = p_intern_id;
END $$

-- GET ALL BY USER ID
CREATE PROCEDURE sp_get_all_by_user_id_documents (IN p_user_id INT)
BEGIN
	SELECT * FROM documents WHERE up_by_id = p_user_id;
END $$

-- UPDATE
CREATE PROCEDURE sp_update_document(
    IN p_id INT,
    IN p_original_name VARCHAR(255),
    IN p_path VARCHAR(500),
    IN p_document_type ENUM('CV','certificate','report','other'),
    IN p_description TEXT,
    IN p_up_by_id INT,
    IN p_active TINYINT(1)
)
BEGIN
    UPDATE documents
    SET original_name = p_original_name,
        path = p_path,
        document_type = p_document_type,
        description = p_description,
        up_by_id = p_up_by_id,
        active = p_active
    WHERE id = p_id;
END $$

-- DELETE (lógico)
CREATE PROCEDURE sp_delete_document(IN p_id INT)
BEGIN
    UPDATE documents
    SET active = 0
    WHERE id = p_id;
END $$

DELIMITER ;


-- ============================================
-- SPs: intern_documents
-- ============================================

DELIMITER $$

-- CREATE
CREATE PROCEDURE sp_create_intern_document(
    IN p_document_id INT,
    IN p_intern_id INT,
    IN p_relation_type VARCHAR(100)
)
BEGIN
    INSERT INTO intern_documents (
        document_id, intern_id, relation_type
    ) VALUES (
        p_document_id, p_intern_id, p_relation_type
    );
END $$

-- GET BY ID
CREATE PROCEDURE sp_get_by_id_intern_document(IN p_id INT)
BEGIN
    SELECT * FROM intern_documents WHERE id = p_id;
END $$

-- GET BY DOCUMENT AND INTERN ID
CREATE PROCEDURE sp_get_by_document_and_intern_id_intern_document(
	IN p_document_id INT,
    IN p_intern_id INT
)
BEGIN
	SELECT * FROM intern_documents
    WHERE
		document_id = p_document_id AND
        intern_id = p_intern_id;
END $$

-- GET ALL
CREATE PROCEDURE sp_get_all_intern_documents()
BEGIN
    SELECT * FROM intern_documents;
END $$

-- UPDATE
CREATE PROCEDURE sp_update_intern_document(
    IN p_id INT,
    IN p_document_id INT,
    IN p_intern_id INT,
    IN p_relation_type VARCHAR(100)
)
BEGIN
    UPDATE intern_documents
    SET document_id = p_document_id,
        intern_id = p_intern_id,
        relation_type = p_relation_type
    WHERE id = p_id;
END $$

-- DELETE (físico)
CREATE PROCEDURE sp_delete_intern_document(IN p_id INT)
BEGIN
    DELETE FROM intern_documents WHERE id = p_id;
END $$

-- DELETE BY DOCUMENT AND INTERN ID
CREATE PROCEDURE sp_delete_by_document_and_intern_id_intern_document(
	IN p_document_id INT,
    IN p_intern_id INT
)
BEGIN
	DELETE FROM intern_documents
    WHERE
		document_id = p_document_id AND
        intern_id = p_intern_id;
END $$

DELIMITER ;
