-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 03, 2025 at 04:05 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anemia_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `diagnosis_results`
--

CREATE TABLE `diagnosis_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `patient_name` varchar(255) DEFAULT 'Anonymous',
  `notes` text DEFAULT NULL,
  `image_filename` varchar(255) NOT NULL,
  `classification` varchar(100) NOT NULL,
  `confidence_score` varchar(10) DEFAULT '0%',
  `explanation` text DEFAULT NULL,
  `health_risk` text DEFAULT NULL,
  `analysis_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnosis_results`
--

INSERT INTO `diagnosis_results` (`id`, `user_id`, `patient_name`, `notes`, `image_filename`, `classification`, `confidence_score`, `explanation`, `health_risk`, `analysis_date`, `created_at`, `updated_at`) VALUES
(2, 4, 'Anonymous', '', 'diagnosis_2025-06-20_13-43-37_685549694ff9c.png', 'Microcytic anemia', '85%', 'The blood test results show that the MCV (Mean Corpuscular Volume) is 70.52 fL, which is below the normal range of 80.00-100.00 fL. Additionally, Hemoglobin is also low (129.20 mg/dL). This indicates that the red blood cells are smaller than normal, suggesting microcytic anemia. The low Hemoglobin further confirms the presence of anemia.', 'Microcytic anemia can be caused by iron deficiency, thalassemia, or other underlying health conditions. It can lead to fatigue, weakness, shortness of breath, and other complications if left untreated. It is recommended to consult with a healthcare professional for further evaluation, including iron studies and other diagnostic tests, to determine the underlying cause and appropriate treatment plan. Iron supplements may be prescribed if iron deficiency is identified.', '2025-06-20 13:43:37', '2025-06-20 11:43:37', '2025-06-20 11:43:37'),
(3, 4, 'Anonymous', '', 'diagnosis_2025-06-25_09-29-26_685ba55664b76.png', 'Normocytic anemia', '75%', 'Based on the provided CBC results, the MCV (Mean Corpuscular Volume) is 90.0 fL, which falls within the normal range (80.00 - 100.00 fL). This indicates a normal red blood cell size. However, the Hemoglobin level is low at 122.0 g/dL (reference range: 140.00 - 160.00 mg/dL), Hematocrit is low at 0.35 % (reference range: 0.42-0.52 %), and RBC Count is high at 6.60 x10(12)/L (reference range: 5.50 - 6.50 x10(12)/L), suggesting normocytic anemia. This type of anemia can occur due to various factors, including chronic disease, acute blood loss, or early iron deficiency.', 'Normocytic anemia can cause fatigue, weakness, shortness of breath, and pale skin. Further investigation is needed to determine the underlying cause and appropriate treatment. It is important to consult with a healthcare professional for a comprehensive evaluation and management plan.', '2025-06-25 09:29:26', '2025-06-25 07:29:26', '2025-06-25 07:29:26'),
(4, 4, 'Kyle Ampil', 'None', 'diagnosis_2025-09-03_03-18-37_68b7976d1b3fa.png', 'Microcytic anemia', '95%', 'The image shows a complete blood count (CBC) result. The key indicators for anemia classification are MCV (Mean Corpuscular Volume), MCH (Mean Corpuscular Hemoglobin), and MCHC (Mean Corpuscular Hemoglobin Concentration). In this case: \n- Hemoglobin is low (129.20 g/dL, reference range 140-160 g/dL). \n- Hematocrit is low (0.48, reference range 0.42-0.52).\n- MCV is low (70.52 fL, reference range 80-100 fL). This indicates microcytic anemia.\n- MCH is within the normal range (29 pg, reference range 27-33 pg).\n- MCHC is within the normal range (32.46 %, reference range 32-36%). \nThe combination of low hemoglobin, hematocrit, and MCV confirms microcytic anemia.', 'Microcytic anemia can be caused by iron deficiency, thalassemia, or other chronic diseases. Potential health risks include fatigue, weakness, shortness of breath, and pale skin. It is recommended to consult a healthcare professional for further evaluation, including iron studies and possibly hemoglobin electrophoresis, to determine the underlying cause and initiate appropriate treatment.', '2025-09-03 03:18:37', '2025-09-03 01:18:37', '2025-09-03 01:18:37');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_account`
--

CREATE TABLE `tbl_account` (
  `acc_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_account`
--

INSERT INTO `tbl_account` (`acc_id`, `email`, `password`) VALUES
(4, 'kyle@gmail.com', '$2y$10$SUGiuMrj3QDQ5CMkDWH7eu5tBejiU5PNq6HoAs.8hlwfXyxV12yJi'),
(5, 'jcdavid@gmail.com', '$2y$10$PU7mjW38sRf9xuywoOpmt.Ao/sT0iQYd01HFwymv3X01VBK3jtNU2'),
(6, 'moya@gmail.com', '$2y$10$HbM0ZVySD1PzQ5WAVQuqCeg5FLjPcGozy9BqjZlYr6nVLtc0Om2pm');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_account_details`
--

CREATE TABLE `tbl_account_details` (
  `acc_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_account_details`
--

INSERT INTO `tbl_account_details` (`acc_id`, `full_name`, `gender`) VALUES
(4, 'kyle', NULL),
(5, 'jcdavid', NULL),
(6, 'moya', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `diagnosis_results`
--
ALTER TABLE `diagnosis_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_account`
--
ALTER TABLE `tbl_account`
  ADD PRIMARY KEY (`acc_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `diagnosis_results`
--
ALTER TABLE `diagnosis_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_account`
--
ALTER TABLE `tbl_account`
  MODIFY `acc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
