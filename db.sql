-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07/11/2024 às 14:49
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

--
-- Banco de dados: `pwa_app`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nome`, `email`, `senha`, `telefone`, `role`, `created_at`) VALUES
(43, 'Eliezer Chaves', 'chaves.eliezer@admin.com', '$2y$10$LLaIjS9ZsI/rBUEGFp7ZsOzDUKW0WJiD0mWm8O0ZQh1Y683w4Xanq', '12999999999', 'admin', '2024-11-06 17:49:48'),
(45, 'Eliezer Chaves', 'chaves.eliezer@user.com', '$2y$10$m/9Deje6HCue8fEzW06s5OIaBLtdyhed374SobSrQDGevgIgj3gsS', '12999999999', 'user', '2024-11-06 21:11:20'),

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
COMMIT;
