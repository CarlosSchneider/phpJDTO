/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `phptest`
--
CREATE DATABASE `phptest`;

USE DATABASE `phptest`;
-- --------------------------------------------------------
-- Estrutura da tabela `childs`

CREATE TABLE `phptest`.`childs` (
  `id`         int(11) NOT NULL AUTO_INCREMENT,
  `parentid`   int(11) NOT NULL,
  `firstname`  varchar(60) NOT NULL,
  `lastname`   varchar(60),
  `age`        int(11),
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estrutura da tabela `persons`

CREATE TABLE `phptest`.`persons` (
  `id`         int(11) NOT NULL AUTO_INCREMENT,
  `firstname`  varchar(60) NOT NULL,
  `lastname`   varchar(60),
  `email`      varchar(80),
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Estrutura da tabela `pets`

CREATE TABLE `phptest`.`pets` (
  `id`       int(11) NOT NULL AUTO_INCREMENT,
  `childid`  int(11) NOT NULL,
  `name`     varchar(60) NOT NULL,
  `type`     varchar(40),
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

CREATE USER 'phpusr'@'%' IDENTIFIED BY '9PmOuDmCbH7EpVmG';
GRANT ALL PRIVILEGES ON phptest.* To 'phpusr'@'%';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
