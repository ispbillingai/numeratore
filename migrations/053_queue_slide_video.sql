-- Migration: 053_queue_slide_video
-- A monitor tile can show a video (uploaded beforehand in Amministrazione › Eliminacode).
-- The monitor downloads it in full before showing it and plays it to the end before
-- moving on. video_audio = play the video's own sound (muted while a number is called).

ALTER TABLE queue_slides
    ADD COLUMN IF NOT EXISTS video_path  VARCHAR(255) NULL DEFAULT NULL AFTER image_path,
    ADD COLUMN IF NOT EXISTS video_audio TINYINT(1)   NOT NULL DEFAULT 0 AFTER video_path;
